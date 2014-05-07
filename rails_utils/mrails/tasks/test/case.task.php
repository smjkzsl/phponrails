<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

if(!isset($options['base_path'])){
    $options['base_path'] = TEST_DIR.DS.TESTING_NAMESPACE;
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

$options['files'] = array();
$suite = '';
foreach ($options as $k => $v){
    if(!in_array($k, $valid_options)){
        if(!is_bool($v)){
            $v = rtrim($v, DS);
            if(strstr($v, DS) || strstr($v, '/')){
                $options['files'][] = $v.'.php';
            }else{
                $suite .= $v.' ';
            }
            unset($options[$k]);
        }
    }
}

if(empty($options['suite']) && !empty($suite)){
    $options['suite'] = trim($suite);
}
//~ var_dump($options);
$options = array_filter($options);

AkUnitTestSuite::runFromOptions($options);
