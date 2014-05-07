<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

require_once(CONTRIB_DIR.DS.'simpletest'.DS.'unit_tester.php');
require_once(CONTRIB_DIR.DS.'simpletest'.DS.'mock_objects.php');
require_once(CONTRIB_DIR.DS.'simpletest'.DS.'reporter.php');
require_once(CONTRIB_DIR.DS.'simpletest'.DS.'web_tester.php');
require_once(CONTRIB_DIR.DS.'simpletest'.DS.'extensions'.DS.'junit_xml_reporter.php');

class AkUnitTest extends UnitTestCase
{
    public
    $app_dir,
    $module = '',
    $insert_models_data = false,
    $instantiate_models = false,
    $rebase = false,
    $skip_fixtures = false;

    private
    $_original_paths = array(),
    $_path_rebased = false;

    public function __construct($label = false) {
        $this->_logOriginalPaths();
        $this->app_dir = AkConfig::getDir('app');
        parent::__construct($label);
        if($this->rebase){
            $this->rebaseAppPaths($this->rebase);
        }
        if(!$this->skip_fixtures){
            $this->_configure();
        }
        $this->_enableAssertions();
    }

    public function __destruct() {
        if($this->_path_rebased){
            $this->restoreAppPaths();
        }
    }

    public function rebaseAppPaths($base_path = null) {
        if(!is_dir($base_path) && $base_path_candidate = AkConfig::getDir('suite', false, false)){
            $base_path = $base_path_candidate;
        }else{
            $base_path = (!is_dir($base_path) ? AkConfig::getDir('fixtures') : $base_path).DS.'app';
        }

        AkConfig::setDir('app',             $base_path);
        AkConfig::setDir('app_installers',  $base_path.DS.'installers');
        AkConfig::setDir('models',          $base_path.DS.'models');
        AkConfig::setDir('controllers',     $base_path.DS.'controllers');
        AkConfig::setDir('views',           $base_path.DS.'views');
        AkConfig::setDir('apis',            $base_path.DS.'apis');
        AkConfig::setDir('fixtures',        $base_path.DS.'fixtures');
        AkConfig::setDir('helpers',         $base_path.DS.'helpers');
        AkConfig::setDir('public',          $base_path.DS.'public');
        $this->_path_rebased = true;
    }

    public function restoreAppPaths() {
        foreach ($this->_original_paths as $type => $original_path){
            AkConfig::setDir($type, $original_path);
        }
    }

    protected function _logOriginalPaths() {
        $this->_original_paths = array(
        'app'               => AkConfig::getDir('app'),
        'models'            => AkConfig::getDir('models'),
        'app_installers'    => AkConfig::getDir('app_installers'),
        'controllers'       => AkConfig::getDir('controllers'),
        'views'             => AkConfig::getDir('views'),
        'apis'              => AkConfig::getDir('apis'),
        'fixtures'          => AkConfig::getDir('fixtures'),
        'helpers'           => AkConfig::getDir('helpers'),
        'public'            => AkConfig::getDir('public'),
        );
    }

    /**
     *    Gets a list of test names. Normally that will
     *    be all internal methods that start with the
     *    name "test". This method should be overridden
     *    if you want a different rule.
     *    @return array        List of test names.
     *    @access public
     */
    public function getTests() {
        $methods = array();
        if (isset($this->skip) && $this->skip == true) {
            return $methods;
        }
        foreach (get_class_methods(get_class($this)) as $method) {
            if ($this->isTest($method)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    protected function _configure() {
        $this->skip = !$this->_checkIfEnabled();
        $this->_loadFixtures();
    }

    protected function _checkIfEnabled($file = null) {
        if ($file == null) {
            $file = isset($this->check_file) ? $this->check_file : null;
        }
        if ($file!=null && file_exists($file)) {
            $val = file_get_contents($file);
            if ($val == '0') {
                return false;
            }
        }
        return true;
    }


    protected function _loadFixtures($loadFixture = null) {
        if (isset($this->fixtures)) {
            $this->fixtures = is_array($this->fixtures)?$this->fixtures:Ak::toArray($this->fixtures);
        } else {
            $this->fixtures = array();
        }

        foreach ($this->fixtures as $fixture) {
            $file = AkConfig::getDir('fixtures').DS.'data'.DS.$fixture.'.yaml';
            if(!file_exists($file)){
                continue;
            }
            if ($loadFixture!=null && $fixture!=$loadFixture) {
                continue;
            }
            $setAlias=false;
            if (!isset($this->$fixture)) {
                $this->$fixture = array();
                $setAlias=true;
                $this->{$fixture.'_set'}=true;
            } else if ($this->{$fixture.'_set'}) {
                $setAlias = true;
            }
            $class_name = AkInflector::classify($fixture);
            if($this->instantiateModel($class_name)){
                $contents = Ak::getStaticVar('yaml_fixture_'.$file);
                if (!$contents) {
                    ob_start();
                    require_once($file);
                    $contents = ob_get_clean();
                    Ak::setStaticVar('yaml_fixture_'.$file, $contents);
                }
                $items = Ak::convert('yaml','array',$contents);
                foreach ($items as $alias=>$item){
                    $obj=$this->{$class_name}->create($item);
                    if (isset($item['created_at'])) {
                        $obj->updateAttribute('created_at',$item['created_at']);
                    } else if (isset($item['created_on'])) {
                        $obj->updateAttribute('created_on',$item['created_on']);
                    }
                    if ($setAlias) {
                        $array=$this->$fixture;
                        $array[$alias] = $obj;
                        $this->$fixture = $array;
                    }
                }
            }
        }
    }

    /**
     * Re-installs the table for a given Modelname and includes or even instantiates the Model.
     * Looks in test/fixtures/app/models for the models and in test/fixtures/app/installers for the appropriate installers.
     * If no class-file for Model is found, it generates a dumb one temporarily.
     * For quick and dirty guys, the table can be generated on the fly. see below.
     *
     * examples:
     * installAndIncludeModels('Article');
     * installAndIncludeModels(array('Article','Comment'=>'id,body'));
     *
     * @param mixed $models
     */
    public function installAndIncludeModels($models = array()) {
        $args = func_get_args();
        $last_arg = count($args)-1;

        if (isset($args[$last_arg]) && is_array($args[$last_arg]) && (isset($args[$last_arg]['instantiate']) || isset($args[$last_arg]['populate']))){
            $options = array_pop($args);
        } else {
            $options = array();
        }
        $default_options = array('instantiate' => true);
        $options = array_merge($default_options, $options);

        $models = !empty($args) ? (is_array($args[0]) ? array_shift($args) : (count($args) > 1 ? $args : Ak::toArray($args[0]))) : array();

        foreach ($models as $key=>$value){                               // handle array('Tag','Article')   <= array
            $model = is_numeric($key) ? $value : $key;                   //  or    array('Tag'=>'id,name'); <= a hash!
            $table_definition = is_numeric($key) ? '' : $value;
            $this->_reinstallModel($model, $table_definition);
            $this->_includeOrGenerateModel($model);
            if($this->insert_models_data || !empty($options['populate'])){
                $this->populateTables(AkInflector::tableize($model));
            }
            if($this->instantiate_models || !empty($options['instantiate'])){
                $this->instantiateModel($model);
            }
        }
    }

    public function log($message) {
        if (LOG_EVENTS){
            static $logger;
            if(empty($logger)) {
                $logger = Ak::getLogger();
            }
            $logger->info('unit-test',$message);
        }
    }

    protected function _reinstallModel($model, $table_definition = '') {
        $this->log('Reinstalling model:'.$model);
        if (!$this->uninstallAndInstallMigration($model)){
            $table_name = AkInflector::tableize($model);
            if (empty($table_definition)) {
                Ak::import($model);
                if(class_exists($model)){
                    $Instance = new $model;
                    if($table_name != $Instance->getTableName()){
                        // skipping, table inheritance
                        return;
                    }
                }
                trigger_error(Ak::t('Could not install the table %tablename for the model %modelname',array('%tablename'=>$table_name, '%modelname'=>$model)),E_USER_ERROR);
                return false;
            }
            $installer = new AkInstaller();
            $installer->skip_db_sql = true;
            $installer->dropTable($table_name, array('sequence'=>true));
            $installer->createTable($table_name,$table_definition, array('timestamp'=>false));
        } else {
            $table_name = AkInflector::tableize($model);
        }
        if (isset($this->fixtures) && is_array($this->fixtures) && in_array($table_name,$this->fixtures)) {
            $this->_loadFixtures($table_name);
        }
    }

    public function uninstallModels($models = array()) {
        foreach ($models as $model){
            $this->uninstallModel($model);
        }
    }

    public function uninstallModel($model) {
        $this->log('Uninstalling model:'.$model);
        if (!$this->uninstallMigration($model)){
            $table_name = AkInflector::tableize($model);
            $installer = new AkInstaller();
            $installer->skip_db_sql = true;
            $installer->dropTable($table_name, array('sequence'=>true));
        }
    }

    public function uninstallAndInstallMigration($installer_name) {
        $installer_name = AkInflector::camelize($installer_name);
        return $this->_uninstallAndInstallMigration($installer_name, true);
    }

    public function uninstallMigration($installer_name) {
        return $this->_uninstallAndInstallMigration($installer_name, false);
    }

    public function dropTables($tables = array()) {
        $installer = new AkInstaller();
        if(is_string($tables) && $tables == 'all'){
            $tables = Ak::db()->getAvailableTables();
        }
        foreach ($tables as $table){
            $installer->dropTable($table, array('sequence'=>true));
        }

    }

    protected function _uninstallAndInstallMigration($installer_name, $reinstall = true) {
        $installer_path = AkConfig::getDir('app_installers').DS.AkInflector::underscore($installer_name).'_installer.php';
        $this->log('Looking for installer:'.$installer_path);
        if (file_exists($installer_path)){
            $this->log('found installer:'.$installer_path);
            require_once($installer_path);
            $installer_class_name = $installer_name.'Installer';
            $Installer = new $installer_class_name();
            $Installer->skip_db_sql = true;
            $Installer->uninstall(null, array('quiet'=>true));
            if($reinstall){
                $Installer->install(null, array('quiet'=>true));
            }
            return true;
        }
        return false;
    }

    protected function _includeOrGenerateModel($model_name) {
        $model_file_name = AkInflector::toModelFilename($model_name);
        if (file_exists($model_file_name)){
            require_once($model_file_name);
        } else {
            if (class_exists($model_name)){
                return true;
            }
            $model_source_code = "class ".$model_name." extends ActiveRecord { }";
            $has_errors = @eval($model_source_code) === false;
            if ($has_errors) trigger_error(Ak::t('Could not declare the model %modelname.',array('%modelname'=>$model_name)),E_USER_ERROR);
        }
    }

    public function populateTables() {
        $args = func_get_args();
        $tables = !empty($args) ? (is_array($args[0]) ? $args[0] : (count($args) > 1 ? $args : Ak::toArray($args))) : array();
        foreach ($tables as $table){
            $file = AkConfig::getDir('fixtures').DS.(empty($this->module)?'':$this->module.DS).Ak::sanitize_include($table).'.yml';
            if(!file_exists($file)){
                continue;
            }
            $class_name = AkInflector::classify($table);
            if($this->instantiateModel($class_name)){
                $contents = Ak::getStaticVar('yaml_fixture_'.$file);
                if (!$contents) {
                    ob_start();
                    require_once($file);
                    $contents = ob_get_clean();
                    Ak::setStaticVar('yaml_fixture_'.$file, $contents);
                }
                $items = Ak::convert('yaml','array',$contents);
                foreach ($items as $item){

                    $obj=$this->{$class_name}->create($item);
                    if (isset($item['created_at'])) {
                        $obj->updateAttribute('created_at',$item['created_at']);
                    } else if (isset($item['created_on'])) {
                        $obj->updateAttribute('created_on',$item['created_on']);
                    }
                }
            }
        }
    }

    public function instantiateModel($model_name) {
        if(class_exists($model_name) || Ak::import($model_name)){
            $this->$model_name = new $model_name();
        } else {
            trigger_error(Ak::t('Could not instantiate %modelname',array('%modelname'=>$model_name)),E_USER_ERROR);
        }
        return !empty($this->$model_name) && is_object($this->$model_name) && strtolower(get_class($this->$model_name)) == strtolower($model_name);
    }

    public function instantiateModels() {
        $args = func_get_args();
        $models = (count($args) > 1) ? $args : Ak::stringToArray(@$args[0]);
        call_user_func_array(array($this, 'instantiateModel'), $models);
    }

    /**
     * Includes and instantiates given models
     */
    public function includeAndInstatiateModels() {
        $args = func_get_args();
        $models = isset($args[1]) ? (array)$args : Ak::toArray($args[0]);
        foreach ($models as $model){
            $this->_includeOrGenerateModel($model);
            $this->instantiateModel($model);
        }
    }

    /**
     * In order to provide compatibility with the defunct assertError method, use this method befor trhowing the error
     */
    public function assertUpcomingError($pattern) {
        $this->expectError(new PatternExpectation('/'.str_replace("'", "\'", preg_quote($pattern)).'/'));
    }

    public function mock($class_name, $returns = array()){

        Mock::generate($class_name);
        $name = 'Mock'.$class_name;
        $Mock = new $name($this);

        foreach ($returns as $method => $value){
            if($value instanceof Exception){
                $Mock->throwOn($method, $value);
            }else{
                $Mock->returnsByValue($method, $value);
            }
        }
        return $Mock;
    }
    public function partialMock($class_name, $methods, $returns = array()){
        $name = 'Mock'.time().Ak::randomString().$class_name;
        Mock::generatePartial($class_name, $name, Ak::toArray($methods));

        $Mock = new $name($this);

        foreach ($returns as $method => $value){
            if($value instanceof Exception){
                $Mock->throwOn($method, $value);
            }else{
                $Mock->returnsByValue($method, $value);
            }
        }
        return $Mock;
    }

    /**
     * Assert that an array contains another array partially
     * 
     * given: array('a'=>'1','b'=>2)
     * 
     * you can now assert that the given contains 'b'=>2
     * 
     * @param array $array
     * @param array $partial_array 
     */
    public function assertArrayContains($array, array $partial_array) {
        $scope = key($partial_array);
        $value = current($partial_array);
        $this->assertTrue(isset($array[$scope]), 'Could not find key '.$scope.' in array '.print_r($array, true));
        $this->assertPattern('/'.$value.'/',@$array[$scope]);
    }

    protected function _enableAssertions(){
    }

    /**
     * PHPUnit compatibility assertions
     */

    public function assertType($type, $instance){
        $this->assertIsA($instance, $type);
    }

    public function assertArrayHasKey($array, $key){
        $this->assertTrue(isset($array[$key]));
    }
}

class AkWebTestCase extends WebTestCase
{
    public function assertWantedText($text, $message = '%s') {
        $this->assertPattern('/'.preg_quote($text).'/', $message);
    }

    /**
     * Asserts only if the whole response matches $text
     */
    public function assertTextMatch($text, $message = '%s') {
        $this->assertPattern('/^'.preg_quote($text).'$/', $message);
    }
}

class RailsTextReporter extends TextReporter
{
    public $time_log = array();
    public $log_time = true;
    public $verbose = false;

    public function __destruct(){
        if($this->log_time) {
            $this->logTestRunime();
        }
    }

    public function paintHeader($test_name) {
        $this->testTag = $test_name;
        $this->testChecksum = md5($test_name);
        $this->timeStart = time();
        $this->testsStart = microtime(true);
        $this->memoryStart = memory_get_usage();
        $this->time_log['group'] = $this->testChecksum;
        $this->time_log['description'] = $this->testTag;
        $this->time_log['total'] = 0;
        $this->time_log['stats'] = array();
        $this->time_log['cases'] = array();
        parent::paintHeader($test_name);
    }

    public function paintFooter($test_name) {
        $duration   = microtime(true) - $this->testsStart;
        $memory     = memory_get_usage() - $this->memoryStart;
        $style = $this->getFailCount() > 0 ? 'error' : ($this->getExceptionCount() > 0 ? 'warning' : 'success');

        if ($this->getFailCount() + $this->getExceptionCount() == 0) {
            print AkAnsiColor::style("\nOK", $style);
        } else {
            $this->log_time = false;
            print AkAnsiColor::style("\nFAILURES!!!", $style);
        }
        $this->time_log['total'] = array($duration, $memory);
        
        print AkAnsiColor::style("\n\nTest cases completed in ".$duration."/s using ".AkNumberHelper::human_size($memory).":\n ". $this->getTestCaseProgress() .
        "/" . $this->getTestCaseCount() .
        ", Passes: " . $this->getPassCount() .
        ", Failures: " . $this->getFailCount() .
        ", Exceptions: " . $this->getExceptionCount() . "\n", $style);
    }

    public function paintCaseStart($case) {
        $this->caseStart = microtime(true);
        $this->caseMemoryStart = memory_get_usage();
        if($this->verbose) echo "$case case:\n\n";
        $this->currentCaseName = $case;
        parent::paintCaseStart($case);
    }

    public function paintCaseEnd($case) {
        $duration = microtime(true) - $this->caseStart;
        $memory     = memory_get_usage() - $this->caseMemoryStart;
        if($this->verbose) print "\n    $duration/s and $memory bytes required to complete $this->currentCaseName\n\n";
        $this->time_log['cases'][$case] = array($duration, $memory);
        parent::paintCaseEnd($case);
    }

    public function paintMethodStart($test) {
        $this->methodStart = microtime(true);
        $this->methodMemoryStart = memory_get_usage();
        if($this->verbose) print " $test ";
    }

    public function paintMethodEnd($test) {
        $duration = microtime(true) - $this->methodStart;
        $memory     = memory_get_usage() - $this->methodMemoryStart;
        $this->time_log['methods'][$test] = array($duration, $memory);
        if($this->verbose) print " completed in $duration using $memory bytes/s\n";
    }
    

    public function paintSkip($message) {
        parent::paintSkip(AkAnsiColor::style($message, 'warning'));
    }

    public function logTestRunime() {
        if(empty($this->time_log['methods'])){
            return ;
        }
        $log_path = LOG_DIR.DS.'unit_test_runtime'.DS.$this->testChecksum.DS.$this->timeStart.'.php';
        $slow_methods = array();
        $memory_hungry_methods = array();
        $stats = array();
        foreach ($this->time_log['methods'] as $method => $details){
            $stats['slow_methods'][$method] = $details[0];
            $stats['memory_hungry_methods'][$method] = $details[1];
        }
        foreach ($this->time_log['cases'] as $case => $details){
            $stats['slow_cases'][$case] = $details[0];
            $stats['memory_hungry_cases'][$case] = $details[1];
        }
        foreach ($stats as $stat_type => $v){
            arsort($stats[$stat_type]);
            $stats[$stat_type] = array_slice($stats[$stat_type], 0, 10, true);
        }
        $this->time_log['stats'] = $stats;

        AkFileSystem::file_put_contents($log_path, '<?php $runtime['.$this->timeStart.'] = '.var_export($this->time_log, true).'; return $runtime['.$this->timeStart.'];');

        @unlink(LOG_DIR.DS.'unit_test_runtime'.DS.'last_run.php');
        link($log_path, LOG_DIR.DS.'unit_test_runtime'.DS.'last_run.php');
    }
}

class RailsVerboseTextReporter extends RailsTextReporter {
    
    public $verbose = true;
    public $paint_skips = true;
    
    public function paintSkip($message) {
        if($this->paint_skips){
            parent::paintSkip($message);
        }
    }
}

class AkXUnitXmlReporter extends SimpleReporter {
    
    private $_fp;

    public function __construct() {
        parent::__construct();
        $this->doc = new DOMDocument();
        $this->doc->loadXML('<testsuite/>');
        $this->root = $this->doc->documentElement;
        $base_path = (defined('MRAILS_BASE_DIR') ? MRAILS_BASE_DIR : BASE_DIR);
        $file_path = AkConfig::getOption('report_path', $base_path.DS.'reports'.DS.'units.xml');
        AkFileSystem::file_put_contents($file_path, "<?xml version=\"1.0\"?>\n", array('base_path' => $base_path));
        $this->_fp = @fopen($file_path, 'a');
    }

    public function __destruct(){
        @fclose($this->_fp);
    }

    public function paintHeader($test_name) {
        $this->testsStart = microtime(true);
        $this->root->setAttribute('name', $test_name);
        $this->root->setAttribute('timestamp', date('c'));
        $this->root->setAttribute('hostname', 'localhost');
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    public function paintFooter($test_name) {
        $duration = microtime(true) - $this->testsStart;
        $this->root->setAttribute('tests', $this->getPassCount() + $this->getFailCount() + $this->getExceptionCount());
        $this->root->setAttribute('failures', $this->getFailCount());
        $this->root->setAttribute('errors', $this->getExceptionCount());
        $this->root->setAttribute('time', $duration);

        $this->doc->formatOutput = true;
        $xml = $this->doc->saveXML();
        // Cut out XML declaration
        @fwrite($this->_fp, preg_replace('/<\?[^>]*\?>/', "", $xml));
    }

    public function paintCaseStart($case) {
        $this->currentCaseName = $case;
    }

    public function paintCaseEnd($case) {
        // No output here
    }

    public function paintMethodStart($test) {
        $this->methodStart = microtime(true);
        $this->currCase = $this->doc->createElement('testcase');
    }

    public function paintMethodEnd($test) {
        $duration = microtime(true) - $this->methodStart;

        $this->currCase->setAttribute('name', $test);
        $this->currCase->setAttribute('classname', $this->currentCaseName);
        $this->currCase->setAttribute('time', $duration);
        $this->root->appendChild($this->currCase);
    }

    public function paintFail($message) {
        parent::paintFail($message);

        error_log("Failure: " . $message);
        $this->terminateAbnormally($message);
    }

    public function paintException($exception) {
        parent::paintException($exception);

        error_log("Exception: " . $exception);
        $this->terminateAbnormally($exception);
    }

    public function terminateAbnormally($message) {
        if (!$this->currCase) {
            error_log("!! currCase was not set.");
            return;
        }

        $ch = $this->doc->createElement('failure');
        $breadcrumb = $this->getTestList();
        $ch->setAttribute('message', $breadcrumb[count($breadcrumb)-1]);
        $ch->setAttribute('type', $breadcrumb[count($breadcrumb)-1]);

        $message = implode(' -> ', $breadcrumb) . "\n\n\n" . $message;
        $content = $this->doc->createTextNode($message);
        $ch->appendChild($content);

        $this->currCase->appendChild($ch);
    }
}
