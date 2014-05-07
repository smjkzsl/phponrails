<?php

defined('AK_ENVIRONMENT')   ||  define('AK_ENVIRONMENT',    'testing');
defined('AK_BASE_DIR')      ||  define('AK_BASE_DIR',       str_replace(DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php','', substr(AK_TEST_DIR,0,-5)));
defined('AK_LOG_EVENTS')    ||  define('AK_LOG_EVENTS',     true);

defined('DS')                   || define('DS',                     DIRECTORY_SEPARATOR);
defined('AK_FRAMEWORK_DIR')     || define('AK_FRAMEWORK_DIR',       AK_BASE_DIR);
defined('AK_TESTING_NAMESPACE') || define('AK_TESTING_NAMESPACE',   'rails');
defined('AK_TESTING_URL')       || define('AK_TESTING_URL',   'http://rails.tests');

include_once AK_FRAMEWORK_DIR.DS.'autoload.php';

if(AK_CLI && !AK_WIN){
    // will try to set the right mode for tmp folders, git does not kee trac of this for us
    foreach ((array)glob(AK_BASE_DIR.DS.'tmp'.DS.'*'.DS.'*/') as $__folder){
        `chmod 777 $__folder`;
    }
    unset($__folder);
}

if(!AkConfig::getOption('testing_url', false))
AkConfig::setOption('testing_url', AK_TESTING_URL);
AkUnitTestSuite::checkIfTestingWebserverIsAccesible(array('base_path' => AK_TEST_DIR.DS.AK_TESTING_NAMESPACE));
AkUnitTestSuite::createTestingDatabaseIfNotAvailable();
AkUnitTestSuite::ensureTmpDirPermissions();

try{
    ob_start();
    if(!class_exists('BaseActionController')){
        class BaseActionController extends AkActionController{ }
    }
    if(!class_exists('ApplicationController')){
        class ApplicationController extends BaseActionController { public $layout = false; }
    }
    if(!class_exists('BaseActiveRecord')){
        class BaseActiveRecord extends AkActiveRecord { }
    }
    if(!class_exists('ActiveRecord')){
        class ActiveRecord extends BaseActiveRecord { }
    }
    ob_get_clean();
}catch(Exception $e){}


