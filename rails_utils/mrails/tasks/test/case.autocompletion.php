<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

$base_files = array();

$base_path   = TEST_DIR.DS.TESTING_NAMESPACE;

$autocomplete_options = array_keys($options);
$matched = false;
$suggestions = array();

if(!empty($autocomplete_options[0])){
    list($suite, $current_case) = explode(DS, $autocomplete_options[0].DS);
    $has_suite = is_dir($base_path.DS.$suite.DS);
    $has_case = $has_suite && file_exists($base_path.DS.$suite.DS.'cases'.DS.$current_case.'.php');
    $cases = array_merge(glob($base_path.DS.$suite.'**'.DS.'cases'.DS.'*'), glob($base_path.DS.$suite.DS.'cases'.DS.'*'));

    foreach ($cases as $k => $case){
        if(file_exists($case)){
            $suggestions[] = trim(str_replace(array($base_path, '.php', 'cases'.DS), '', $case), DS);
        }
    }

    if($has_case){
        $case_contents = file_get_contents($base_path.DS.$suite.DS.'cases'.DS.$current_case.'.php');
        if(preg_match_all('/function test_([A-Z0-9_]+)/i', $case_contents, $matches)){
            foreach ($matches[1] as $match){
                if($match != 'start'){
                    $suggestions[] = $suite.'/'.$current_case.'/'.$match;
                }
            }
        }
    }

    $suites = glob($base_path.DS.'*');
    foreach ($suites as $k => $suite){
        if(is_dir($suite)){
            $suggestions[] = trim(str_replace($base_path, '', $suite), DS).'/';
        }
    }

}elseif(empty($suggestions)){
    $suites = glob($base_path.DS.'*');
    foreach ($suites as $k => $suite){
        if(is_dir($suite)){
            $suggestions[] = trim(str_replace($base_path, '', $suite), DS);
        }
    }
}

if(count($suggestions) > 80){
    foreach ($suggestions as $k => $v){
        $suggestions[$k] = $v = trim($v, '/');
        if(strstr($v, '/')){
            unset($suggestions[$k]);
        }
    }
}

echo join("\n", $suggestions);
