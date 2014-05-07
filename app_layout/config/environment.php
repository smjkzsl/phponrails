<?php
date_default_timezone_set("Asia/Shanghai");
//for sina app cloud
if (defined('SAE_TMP_PATH')){
    defined('IN_SAE')||define('IN_SAE',true);

    if(isset($_SERVER['TMP'])){
        define('TMP_DIR',preg_replace('|\/+|',DS, $_SERVER['TMP'] ));
    }else
        define('TMP_DIR',SAE_TMP_PATH );
    define('DOMAIN',preg_replace('|\/+|',DS, $_SERVER['HTTP_HOST'] ));//获取当前域名 for sae cloud
}else{
  defined('IN_SAE')||define('IN_SAE',false);
 }
defined('DS')                   || define('DS',                     DIRECTORY_SEPARATOR);
//BASE_DIR 指网站根目录
defined('BASE_DIR')          || define('BASE_DIR',            str_replace(DS."config","",dirname(__FILE__)) );
defined('RAILS_DIR') || define('RAILS_DIR',
	BASE_DIR.DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."rails");

defined('TESTING_NAMESPACE') || define('TESTING_NAMESPACE',   'rails');

include RAILS_DIR.DS.'autoload.php';


/**
 * After including autoload.php, you can override configuration options by calling:
 * 
 *     AkConfig::setOption('option_name', 'value');
 */

// Rails only shows debug messages if accessed from the localhost IP, you can manually tell
// Rails which IP's you consider to be local.
// AkConfig::setOption('local_ips', array('127.0.0.1', '192.168.1.69'));
AkConfig::setOption('action_controller.session', array("key" => "_data",  "secret" => "1efde661-c4a8-4874-49db-c5e8398bdaaf"));
