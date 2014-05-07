<?php

/**
 * This file is will include Rails autoload.php where the framework makes most of Rails
 * environment guessing. You can override most Rails constants by declaring them in 
 * this file.
 *
 * You can retrieve a list of current settings by running AkDebug::get_constants();
 *
 * If you're running a high load site you might want to fine tune this options
 * according to your environment. If you set the options implicitly you might
 * gain in performance but loose in flexibility when moving to a different
 * environment.
 *
 * If you need to customize the framework default settings or specify
 * internationalization options, edit the files at config/environments/*
 */

defined('DS')                   || define('DS',                     DIRECTORY_SEPARATOR);
defined('BASE_DIR')          || define('BASE_DIR',            str_replace(DS.'config'.DS.'environment.php','',__FILE__));
defined('RAILS_DIR')     || define('RAILS_DIR',       BASE_DIR);
defined('TESTING_NAMESPACE') || define('TESTING_NAMESPACE',   'rails');

include RAILS_DIR.DS.'autoload.php';

/**
 * After including autoload.php, you can override configuration options by calling:
 * 
 *     AkConfig::setOption('option_name', 'value');
 */

// Rails only shows debug messages if accessed from the localhost IP, you can manually tell
// Rails which IP's you consider to be local.
// AkConfig::setOption('local_ips', array('127.0.0.1', '192.168.1.69', '::1'));
