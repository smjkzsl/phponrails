<?php

/**
 * @todo Pass cache tests
 */
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class ActionCaching_TestCase extends AkTestApplication
{
    public $lastModified;

    public function test_init()
    {
        $this->rebaseAppPaths();

        $settings = Ak::getSettings('caching', false);
        if (!isset($settings['handler']['options']['cacheDir'])) {
            $cacheDir = CACHE_DIR.DS;
        } else {
            $cacheDir = $settings['handler']['options']['cacheDir'];
        }
        //chmod($cacheDir,0777);
        $this->_flushCache('rails.org');
        $this->_flushCache('www.rails.org');
        $this->_flushCache('xinc.eu');
        $this->_flushCache('www.example.com');
    }

    public function test_simple_action_cache()
    {
        $this->_flushCache('www.example.com');
        $cache_this = date('Y-m-d, H:i:s');

        $this->setIp('212.121.121.121');
        $this->get('http://www.example.com/action_caching/index', array(), array(), array('cache_this'=>$cache_this));
        $this->assertTextMatch($cache_this);
        $this->assertResponse(200);
        $this->_assertCacheExists('/'.Ak::lang().'/action_caching/index', array('host'=>'www.example.com'));
    }

    public function test_action_cache_with_custom_cache_path()
    {
        $this->_flushCache('test.host');
        $cache_this = date('Y-m-d, H:i:s');
        $this->get('http://www.example.com/action_caching/show',array(),array(),array('cache_this'=>$cache_this));
        $this->_assertCacheExists('/custom/show', array('host'=>'test.host'));
        $cached = $this->_getActionCache('/custom/show', array('host'=>'test.host'));
        $this->assertTextMatch($cache_this);
        $this->assertEqual($cache_this, $cached);
    }

    public function test_action_cache_with_custom_cache_path_in_block()
    {
        $cache_this = date('Y-m-d, H:i:s');

        $this->get('http://www.example.com/action_caching/edit',array(),array(),array('cache_this'=>$cache_this));
        $this->_assertCacheExists('/edit', array('host'=>'test.host'));

        $this->get('http://www.example.com/action_caching/edit/1',array(),array(),array('cache_this'=>$cache_this));
        $this->_assertCacheExists('/1;edit', array('host'=>'test.host'));
    }
    public function test_cache_skip()
    {
        $this->_flushCache('www.example.com');
        $this->get('http://www.example.com/action_caching/skip',array(),array(),array());
        $this->assertTextMatch('Hello<!--CACHE-SKIP-START-->

        You wont see me after the cache is rendered.

        <!--CACHE-SKIP-END-->');
        $this->get('http://www.example.com/action_caching/skip',array(),array(),array());
        $this->assertTextMatch('Hello');
    }
    public function test_cache_expiration()
    {
        $this->_flushCache('www.example.com');
        $time = time();
        $cache_this = date('Y-m-d, H:i:s',$time);

        $this->get('http://www.example.com/action_caching/',array(),array(),array('cache_this'=>$cache_this));
        $this->assertTextMatch($cache_this);
        $this->_assertCacheExists('/'.Ak::lang().'/action_caching/index');

        $cache_this_new = date('Y-m-d, H:i:s',$time+10);
        $this->get('http://www.example.com/action_caching/',array(),array(),array('cache_this'=>$cache_this_new));
        $this->assertHeader('X-Cached-By','Rails-Action-Cache');
        $this->assertTextMatch($cache_this);

        $this->get('http://www.example.com/action_caching/expire');
        $this->assertResponse(200);
        $this->_assertCacheNotExists('/'.Ak::lang().'/action_caching/index');

        $cache_this_new = date('Y-m-d, H:i:s',$time+20);
        $this->get('http://www.example.com/action_caching/',array(),array(),array('cache_this'=>$cache_this_new));
        $this->assertTextMatch($cache_this_new);
        $cached = $this->_getActionCache('/'.Ak::lang().'/action_caching/index');
        $this->assertEqual($cache_this_new, $cached);

    }

    public function test_cache_is_scoped_by_subdomain()
    {
        $this->_flushCache('rails.org');
        $this->_flushCache('www.rails.org');
        $this->_flushCache('xinc.eu');

        $cache_this_rails = date('Y-m-d, H:i:s', time());
        $this->get('http://rails.org/action_caching/',array(),array(),array('cache_this'=>$cache_this_rails));
        $rails_cached = $this->_getActionCache('/'.Ak::lang().'/action_caching/index',array('host'=>'rails.org'));
        $this->assertTextMatch($rails_cached);

        $cache_this_www_rails = date('Y-m-d, H:i:s', time()+10);
        $this->get('http://www.rails.org/action_caching/',array(),array(),array('cache_this'=>$cache_this_www_rails));
        $www_rails_cached = $this->_getActionCache('/'.Ak::lang().'/action_caching/index',array('host'=>'www.rails.org'));
        $this->assertTextMatch($cache_this_www_rails);

        $this->assertNotEqual($rails_cached,$www_rails_cached);

        $cache_this_www_rails_new = date('Y-m-d, H:i:s', time()+20);
        $this->get('http://www.rails.org/action_caching/',array(),array(),array('cache_this'=>$cache_this_www_rails));
        $this->assertTextMatch($cache_this_www_rails);


        $cache_this_xinc = date('Y-m-d, H:i:s', time()+30);
        $this->get('http://xinc.eu/action_caching/',array(),array(),array('cache_this'=>$cache_this_xinc));
        $xinc_cached = $this->_getActionCache('/'.Ak::lang().'/action_caching/index',array('host'=>'xinc.eu'));
        $this->assertTextMatch($cache_this_xinc);


    }

    public function test_redirect_is_not_cached()
    {
        $this->get('http://www.example.com/action_caching/redirected');
        $this->_assertCacheNotExists('/'.Ak::lang().'action_caching/redirected');
    }

    public function test_forbidden_is_not_cached()
    {
        $this->get('http://www.example.com/action_caching/forbidden');
        $this->_assertCacheNotExists('/'.Ak::lang().'action_caching/forbidden');

    }

    public function test_correct_content_type_is_returned_for_cache_hit()
    {
        $cache_this = 'xml';
        $cache_this_rss = 'rss';
        $this->get('http://www.example.com/action_caching/index.xml',array(),array(),array('cache_this'=>$cache_this));
        $this->assertHeader('Content-Type','application/xml');
        $this->get('http://www.example.com/action_caching/index.xml',array(),array(),array('cache_this'=>$cache_this));
        $this->assertHeader('Content-Type','application/xml');
        $this->assertTextMatch('xml');
        $this->get('http://www.example.com/action_caching/index.rss',array(),array(),array('cache_this'=>$cache_this_rss));
        $this->get('http://www.example.com/action_caching/index.rss',array(),array(),array('cache_this'=>$cache_this_rss));
        $this->assertHeader('Content-Type','application/rss+xml');
        $this->assertTextMatch('rss');
        $this->_assertCacheExists('/'.Ak::lang().'/action_caching/index.rss');
    }


    public function test_file_extensions()
    {
        $cache_this = 'text';
        $this->get('http://www.example.com/action_caching/index/kitten.jpg',array(),array(),array('cache_this'=>$cache_this));
        $this->assertHeader('Content-Type','image/jpeg');
        $this->_assertCacheExists('/'.Ak::lang().'/action_caching/index/kitten.jpg',array(),array('host'=>'www.example.com'));
    }

    public function test_normalized_action_paths()
    {
        $this->assertTrue(true,'Need to test that /page is the same cache as /page/index');
        $this->_flushCache('xinc.eu');
        $cache_this_xinc = date('Y-m-d, H:i:s', time()+30);
        $this->get('http://xinc.eu/action_caching/',array(),array(),array('cache_this'=>$cache_this_xinc));
        $xinc_cached_normalized = $this->_getActionCache('/'.Ak::lang().'/action_caching/index',array('host'=>'xinc.eu'));
        $this->assertTextMatch($cache_this_xinc);
        $this->assertEqual($cache_this_xinc,$xinc_cached_normalized);

        $this->get('http://xinc.eu/action_caching/index',array(),array(),array('cache_this'=>$cache_this_xinc));
        $this->assertTextMatch($cache_this_xinc);
    }


    private function _getActionCache($path, $options = array())
    {
        $controller = $this->getController();
        $options['action_cache'] = true;
        $options['namespace'] = 'actions';
        $fragment = $controller->readFragment($path, $options);
        return $fragment;
    }

    private function _assertCacheExists($path, $options = array())
    {
        $options['namespace'] = 'actions';
        $fragment = $this->_getActionCache($path, $options);
        $this->assertTrue($fragment!==false);
    }

    private function _assertCacheNotExists($path, $options = array())
    {
        $fragment = $this->_getActionCache($path, $options);
        $this->assertTrue($fragment===false);
    }

    private function _flushCache($host)
    {
        $settings = Ak::getSettings('caching',false);
        $file_cache = AkCache::lookupStore($settings);
        if ($file_cache !== false) {
            $file_cache->clean($host);
        }
    }

}

ak_test_case('ActionCaching_TestCase');

