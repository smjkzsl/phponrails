<?php

defined('CACHE_HANDLER')         || define('CACHE_HANDLER', 1);
defined('ENVIRONMENT')           || define('ENVIRONMENT', 'testing');
defined('TEST_DIR')              || define('TEST_DIR', str_replace(DIRECTORY_SEPARATOR.'shared'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php','',__FILE__));
defined('FIXTURES_DIR')          || define('FIXTURES_DIR', TEST_DIR.DIRECTORY_SEPARATOR.'fixtures');

if(isset($_SERVER['REQUEST_URI'])){
    defined('SITE_URL_SUFFIX')   || define('SITE_URL_SUFFIX', str_replace(array(join(DIRECTORY_SEPARATOR,array_diff((array)@explode(DIRECTORY_SEPARATOR,TEST_DIR), (array)@explode('/',$_SERVER['REQUEST_URI']))),DIRECTORY_SEPARATOR),array('','/'),TEST_DIR));
}else{
    defined('SITE_URL_SUFFIX')   || define('SITE_URL_SUFFIX', '/');
}
defined('ENABLE_AKELOS_ARGS')    ||  define('ENABLE_AKELOS_ARGS', true);
defined('URL_REWRITE_ENABLED')   ||  define('URL_REWRITE_ENABLED', true);

$_app_config_file = substr(TEST_DIR,0,-5).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
include_once(file_exists($_app_config_file) ? $_app_config_file : 'app_config.php');

defined('APP_LOCALES')           ||  define('APP_LOCALES', 'en,es');
defined('PUBLIC_LOCALES')        ||  define('PUBLIC_LOCALES', APP_LOCALES);

defined('ACTIVE_RECORD_ENABLE_AUTOMATIC_SETTERS_AND_GETTERS')    ||  define('ACTIVE_RECORD_ENABLE_AUTOMATIC_SETTERS_AND_GETTERS', true);

