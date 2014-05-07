<?php

// Define constants that are used only on a testing environment
// See file environment.php for more info

ini_set('date.timezone', 'UTC');
define('DIE_ON_TRIGGER_ERROR', true);

ini_set('display_errors', 1);
ini_set('memory_limit', -1);
ini_set('log_errors', 0);

error_reporting(-1);

include ACTIVE_SUPPORT_DIR.DS.'error_handlers'.DS.'testing.php';

