<?php

require_once(dirname(__FILE__).'/../config.php');

class Cache_TestCase extends  ActiveSupportUnitTest
{
    public $DriverInstance = NULL;
    public $Cache = NULL;
    public $id = 'test case cache id';
    public $group = 'test case group to cacth';
    public $text_to_catch = 'this is the text to catch on the test case of the AkCache class';


    public function test_all_caches() {
        $cacheHandlers = array('cache_lite'=>1,'akadodbcache'=>2);
        $memcacheEnabled = AkConfig::getOption('memcached_enabled', false);
        AkUnitTestSuite::createTestingDatabaseIfNotAvailable();
        if ($memcacheEnabled) {
            $cacheHandlers['akmemcache'] = 3;
        }

        $unitTests = array('_testInit', '_getAndSaveTests', '_removeTests', '_cleanTests');

        foreach ($cacheHandlers as $class=>$type) {
            foreach ($unitTests as $test) {
                unset($this->Cache);
                $this->Cache = new AkCache();
                $this->$test($type, $class);
            }
            $this->Cache->clean($this->group);
        }
    }

    public function _testInit($type, $class) {
        //No driver is loaded
        $this->Cache->init(null, 0);
        $this->assertNull($this->Cache->DriverInstance,'Checking that no driver is loaded when cache is disabled');

        //Pear Cache Lite driver is loaded
        $this->Cache->init(null, $type);
        $this->assertIsA($this->Cache->DriverInstance, $class);

    }

    public function _getAndSaveTests($type, $class) {
        //No cache
        $this->Cache->init(null, 0);
        $data = $this->Cache->get('id');
        $this->assertFalse($data,'Cache not enabled so this must return false');
        $this->assertFalse(!$this->Cache->save($this->text_to_catch, $this->id, $this->group),'saving on the file cache must not work because cache is disabled');

        $this->Cache->init(2, $type);
        $data = $this->Cache->get($this->id, $this->group);
        $this->assertFalse($data,'This id must not be in the cache (Cache class:'.$class.')');
        $this->assertFalse(!$this->Cache->save($this->text_to_catch, $this->id, $this->group),'saving the  cache (Cache class:'.$class.')');
        $this->Cache->init(2, $type);
        $data = $this->Cache->get($this->id, $this->group);
        $this->assertEqual($data, $this->text_to_catch,'Getting cached data (Cache class:'.$class.')');
        sleep(4);
        $this->Cache->init(2, $type);
        $data = $this->Cache->get($this->id, $this->group);
        $this->assertFalse($data,'The cache has expired and we recognize it (Cache class:'.$class.')');
    }

    public function _removeTests($type,$class) {

        $this->Cache->init(1,0);
        $this->assertFalse(!$this->Cache->remove($this->id, $this->group),'Removing cached file (Cache disabled must return success)');

        $this->Cache->init(3, $type);
        $this->assertFalse(!$this->Cache->save($this->text_to_catch, $this->id, $this->group),'saving the cache (Cache class:'.$class.')');
        $this->Cache->init(2, $type);
        $data = $this->Cache->get($this->id, $this->group);
        $this->assertEqual($data, $this->text_to_catch,'Checking that cached data has been inserted (Cache class:'.$class.')');
        $this->assertFalse(!$this->Cache->remove($this->id, $this->group),'Removing cached file (Cache class:'.$class.')');
        $data = $this->Cache->get($this->id, $this->group);
        $this->assertFalse($data,'The cache must have been removed at this point but stills here (Cache class:'.$class.')');
    }

    public function _cleanTests($type, $class) {
        $this->Cache->init(null,$type);
        $this->assertFalse(!$this->Cache->save($this->text_to_catch, $this->id, $this->group),'saving ('.$class.' based)');
        $this->Cache->init(null,$type);
        $data = $this->Cache->get($this->id, $this->group);
        $this->assertEqual($data, $this->text_to_catch,'Checking that cached data has been inserted ('.$class.' based)');

        $this->Cache->init(null,$type);
        $this->assertFalse(!$this->Cache->clean($this->group),'Removing all the items in cache('.$class.' based)');

        $this->Cache->init(null,$type);
        $data = $this->Cache->get($this->id, $this->group);
        $this->assertFalse($data,'The cache must have been removed at this point but stills here('.$class.' based)');

    }
}

ak_test_case('Cache_TestCase');
