<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details
function getWinPHPExe(){
    if(WIN){
	$paths=$_ENV ['Path'];
	foreach(explode(';',$paths) as $p){
		$file=$p.DS."php.exe";
		if(file_exists($file))
			return $file;
	}
	return 'D:\SaeServer\php\php.exe';
    }
}

if(defined('CONSOLE_MODE')){

    require_once(ACTIVE_SUPPORT_DIR.DS.'base.php');
    require_once CONTRIB_DIR.DS.'iphp'.DS.'iphp.php';

    iphp::main(array(
    'php_bin'=>WIN? getWinPHPExe() : '',
    'require'       => __FILE__,
    'prompt_header' => "Rails PHP Framework iphp console\n"
    ));

}else{

    define('CONSOLE_MODE', true);
    defined('DS')           || define('DS', DIRECTORY_SEPARATOR);
    //~ defined('BASE_DIR')  || define('BASE_DIR', $_SERVER['PWD']);
    defined('BASE_DIR')  || define('BASE_DIR','D:\SaeServer\www');

    $_app_config_file = BASE_DIR.DS.'config'.DS.'config.php';

    if(file_exists($_app_config_file)){
        include(BASE_DIR.DS.'config'.DS.'config.php');
    }else{
        include(BASE_DIR.DS.'test'.DS.'shared'.DS.'config'.DS.'config.php');
    }
    defined('ENVIRONMENT')           || define('ENVIRONMENT', 'testing');

    require_once(ACTIVE_SUPPORT_DIR.DS.'base.php');

}
