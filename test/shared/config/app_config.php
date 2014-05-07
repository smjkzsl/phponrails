<?php

defined('ENVIRONMENT')   ||  define('ENVIRONMENT',    'testing');
defined('BASE_DIR')      ||  define('BASE_DIR',       str_replace(DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php','', substr(TEST_DIR,0,-5)));
defined('LOG_EVENTS')    ||  define('LOG_EVENTS',     true);

defined('DS')                   || define('DS',                     DIRECTORY_SEPARATOR);
defined('RAILS_DIR')     || define('RAILS_DIR',       BASE_DIR);
defined('TESTING_NAMESPACE') || define('TESTING_NAMESPACE',   'rails');
defined('TESTING_URL')       || define('TESTING_URL',   'http://rails.tests');//some tests(like url request) need http server ruing

include_once RAILS_DIR.DS.'autoload.php';

if(CLI && !WIN){
    // will try to set the right mode for tmp folders, git does not kee trac of this for us
    foreach ((array)glob(BASE_DIR.DS.'tmp'.DS.'*'.DS.'*/') as $__folder){
        `chmod 777 $__folder`;
    }
    unset($__folder);
}

if(!AkConfig::getOption('testing_url', false))
AkConfig::setOption('testing_url', TESTING_URL);
AkUnitTestSuite::checkIfTestingWebserverIsAccesible(array('base_path' => TEST_DIR.DS.TESTING_NAMESPACE));
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


