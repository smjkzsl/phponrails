<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

if(isset($options['ci'])){
    unset($options['ci']);
    $options['reporter'] = 'AkXUnitXmlReporter';
}

if(isset($options['verbose'])){
    unset($options['verbose']);
    $options['reporter'] = 'RailsVerboseTextReporter';
}

if($db_type = empty($options['db']) ? false :  $options['db']){
    define('DATABASE_SETTINGS_NAMESPACE', $db_type);
    unset($options['db']);
}
$valid_options = array('config', 'base_path', 'namespace', 'TestSuite', 'reporter', 'files', 'on_success', 'on_failure');

$options['files'] = array();
$component = '';
foreach ($options as $k => $v){
    if(!in_array($k, $valid_options)){
        if(!is_bool($v)){
            $v = rtrim($v, DS);
            if(strstr($v, DS)){
                $options['files'][] = $v.'.php';
            }else{
                $component .= $v.',';
            }
            unset($options[$k]);
        }
    }
}

if(empty($options['component']) && !empty($component)){
    $options['component'] = trim($component, ', ');
}

$options = array_diff($options, array(''));
$options['component'] = empty($options['component']) ? AkConfig::getOption('component', 
file_exists(CONFIG_DIR.DS.'config.php') ? AkInflector::underscore(APP_NAME) : 'rails'
) : $options['component'];

AkUnitTestSuite::runFromOptions($options);
