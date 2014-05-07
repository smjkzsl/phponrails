<?php

// Define constants that are used only on a development environment
// See file environment.php for more info

@ini_set('display_errors', 1);
error_reporting(-1);

define('DEBUG', true);
//define('LOG_EVENTS', true);
include ACTIVE_SUPPORT_DIR.DS.'error_handlers'.DS.'development.php';

// Variable configuration options can be set at this point by calling
//AkConfig::setOption('options', 'value');