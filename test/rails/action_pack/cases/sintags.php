<?php

require_once(dirname(__FILE__).'/../config.php');

class Sintags_TestCase extends ActionPackUnitTest
{
    public function test_setup() {
        define('SINTAGS_AVAILABLE_HELPERS', 'a:9:{s:7:"url_for";s:10:"url_helper";s:7:"link_to";s:10:"url_helper";s:7:"mail_to";s:10:"url_helper";s:10:"email_link";s:10:"url_helper";s:9:"translate";s:11:"text_helper";s:20:"number_to_human_size";s:13:"number_helper";s:6:"render";s:10:"controller";s:25:"distance_of_time_in_words";s:11:"date_helper";s:1:"h";s:11:"text_helper";}');
        new AkSintags();
    }

    public function _test_sintags() {
        $this->_run_from_file('sintags_test_data.txt');
    }
    public function test_sintags_helpers() {
        AkRouterHelper::generateHelperFunctionsFor('named_route', $this->mock('AkRoute'));
        $this->_run_from_file('sintags_helpers_data.txt');
    }

    public function test_sintags_blocks() {
        $this->_run_from_file('sintags_blocks_data.txt');
    }

    public function _run_from_file($file_name, $all_in_one_test = true) {
        $multiple_expected_php = $multiple_sintags = '';
        $tests = explode('===================================',file_get_contents(AkConfig::getDir('fixtures').DS.$file_name));
        foreach ($tests as $test) {
            list($sintags, $php) = explode('-----------------------------------',$test);
            $sintags = trim($sintags);
            $expected_php = trim($php);
            if(empty($sintags)){
                return;
            }else{
                $multiple_sintags .= $sintags;
                $multiple_expected_php .= $expected_php;
            }
            $AkSintags = new AkSintagsParser();
            $php = $AkSintags->parse($sintags);
            if($php != $expected_php){
                AkDebug::trace("GENERATED: \n".$php);
                AkDebug::trace("EXPECTED: \n".$expected_php);
                AkDebug::trace("SINTAGS: \n".$sintags);
            }

            $this->assertEqual($php, $expected_php);
        }

        if($all_in_one_test){
            $AkSintags = new AkSintagsParser();
            $php = $AkSintags->parse($multiple_sintags);
            if($php != $multiple_expected_php){
                AkDebug::trace("GENERATED: \n".$php);
                AkDebug::trace("EXPECTED: \n".$expected_php);
                AkDebug::trace("SINTAGS: \n".$sintags);
            }
            $this->assertEqual($php, $multiple_expected_php);
        }
    }
}


ak_test_case('Sintags_TestCase');

