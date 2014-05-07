<?php

// Core tests need to run without installing a new app
$_config_path_candidate = realpath(dirname(__FILE__).'/../../../../app_layout/config');
if($_config_path_candidate){
    define('CONFIG_DIR', $_config_path_candidate);
}
unset($_config_path_candidate);

define('ENABLE_URL_REWRITE',     false);
define('URL_REWRITE_ENABLED',    false);

if(isset($_GET['custom_routes'])){
    define('ROUTES_MAPPING_FILE', dirname(__FILE__).'/../'.str_replace('.','',$_GET['custom_routes']).'_routes.php');
}

require_once(dirname(__FILE__).'/../config.php');

// We need first to rebase the application
$UnitTest = new ActionPackUnitTest();

$Dispatcher = new AkDispatcher();
$Dispatcher->dispatch();


