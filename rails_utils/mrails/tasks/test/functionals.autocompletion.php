<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

$base_files = array();

$base_path   = TEST_DIR.DS.'functional'.DS.'controllers';

$controllers = glob($base_path.DS.'*_controller_test.php');
foreach ($controllers as $k => $controller){
    if(is_file($controller)){
        $suggestions[] = trim(str_replace(array($base_path, DS, '_controller_test.php'), '', $controller), DS);
    }
}

echo join("\n", $suggestions);
