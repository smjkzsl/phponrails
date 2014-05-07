<?php

if(!defined('BASE_DIR') && !defined('RAILS_DIR'))
{
    define('RAILS_DIR', realpath(dirname(__FILE__).'/../../'));
    if(is_dir(RAILS_DIR.DIRECTORY_SEPARATOR.'app_layout'))
    {
        define('BASE_DIR', RAILS_DIR.DIRECTORY_SEPARATOR.'app_layout');
    }
}

require_once(dirname(__FILE__).'/../shared/config/config.php');

AkConfig::setOption('testing_url', 'http://rails.tests/rails');
AkConfig::setOption('action_controller.session', array("key" => "_myapp_session", "secret" => "c1ef4792-42c5-b484-819e-16750c71cddb"));

AkUnitTestSuite::checkIfTestingWebserverIsAccesible(array('base_path' => dirname(__FILE__)));
AkConfig::setOption('memcached_enabled', AkMemcache::isServerUp());

if(WEB_REQUEST && !(REMOTE_IP == '127.0.0.1' || REMOTE_IP == '::1')){
    die('Web tests can only be called from localhost(127.0.0.1), you can change this beahviour in '.__FILE__);
}