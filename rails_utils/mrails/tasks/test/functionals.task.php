<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

if(!isset($options['base_path'])){
    $options['base_path'] = TEST_DIR.DS.'functional'.DS.'controllers';
}

if(isset($options['ci'])){
    unset($options['ci']);
    $options['reporter'] = 'JUnitXMLReporter';
}

if(isset($options['verbose'])){
    unset($options['verbose']);
    $options['reporter'] = 'RailsVerboseTextReporter';
}

if($reporter = empty($options['reporter']) ? false :  $options['reporter']){
    unset($options['reporter']);
}

if($db_type = empty($options['db']) ? false :  $options['db']){
    define('DATABASE_SETTINGS_NAMESPACE', $db_type);
    unset($options['db']);
}


if(empty($options)){
    $Logger->message('Invalid test name');
    echo "Invalid test name\n";
    return false;
}

$valid_options = array('config', 'base_path', 'namespace', 'TestSuite', 'reporter'  => 'TextReporter', 'files');

$controllers = array();
foreach ($options as $k => $v){
    if(!in_array($k, $valid_options)){
        if(!is_bool($v)){
            $v = rtrim($v, DS);
            $options['files'][] = $options['base_path'].DS.$v.'_controller_test.php';
            unset($options[$k]);
        }
    }
}
if(empty($options['files'])){
    $controller_files = glob($options['base_path'].DS.'*_controller_test.php');
    foreach ($controller_files as $k => $controller){
        if(is_file($controller)){
            $options['files'][] = $controller;
        }
    }
}

if(empty($options['description']) && !empty($options['files'])){
    $description = array();
    foreach ($options['files'] as $file){
        $description[] = trim(str_replace(array($options['base_path'], DS, '_controller_test.php'), '', $file), DS);
    }
    $options['description'] = AkTextHelper::pluralize(count($description), 'Fixture for controller ','Fixtures for controllers ').'('.join(', ', $description).')';
}

AkUnitTestSuite::runFunctionalFromOptions($options);
