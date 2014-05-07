<?php

require_once(dirname(__FILE__).'/../config.php');

class ActiveDocumentUnitTest extends AkUnitTest
{
    public function __construct(){
        AkConfig::setDir('suite', dirname(__FILE__));
        $this->rebaseAppPaths();
        $this->db = new AkOdbAdapter();
        $this->db->connect(array('type' => 'mongo_db', 'database' => 'rails_testing'));
        defined('TESTING_MONGO_DB_IS_CONNECTED') || define('TESTING_MONGO_DB_IS_CONNECTED', $this->db->isConnected());
    }

    public function __destruct(){
        parent::__destruct();
    }
    
    public function skip(){
        $this->skipIf(!TESTING_MONGO_DB_IS_CONNECTED, '[' . get_class($this) . '] '.'Can\'t connect to MongoDB');
    }
}

AkConfig::setOption('document_connections', array(
array('type' => 'mongo_db')
));