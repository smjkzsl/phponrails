<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
 * This class provides an interface for dispatching a request
 * to the appropriate controller and action.
 */
class AkDispatcher
{
    public $Request;
    public $Response;
    public $Controller;

    public function dispatch() {
        if(IN_SAE && isset($_GET['ak']) && strstr($_GET['ak'],'?'))
            $_GET=$this->getParams($_GET['ak']);//sae 真变态
        try{
            if(!$this->dispatchCached()){
                $time_start = microtime(true);
                ENABLE_PROFILER &&  AkDebug::profile(__CLASS__.'::'.__FUNCTION__.'() call');
                $this->Request = AkRequest::getInstance();
                $this->Response = new AkResponse();
                if($this->Controller = $this->Request->recognize()){
                    $this->Controller->ak_time_start = $time_start;
                    ENABLE_PROFILER && AkDebug::profile('Request::recognize() completed');
                    $this->Controller->process($this->Request, $this->Response);
                }
            }
        }catch(Exception $e){
            if(isset($this->Controller) && method_exists($this->Controller, 'render_error')){
                $this->Controller->render_error($e);
            }else{
                $ExceptionDispatcher = new AkExceptionDispatcher();
                $ExceptionDispatcher->renderException($e);
            }
        }
    }
	/*
	*sina sae cloud only
	*split $URL string to $GET
	
	*/
    function getParams($url){
	$arr = array();
        $refer_url = parse_url($url);
	$arr['ak']=$refer_url['path'];
	if(!isset($refer_url['query'])){
		return $arr;
	}
        $params = $refer_url['query'];
        if(!empty($params))	{
            $paramsArr = explode('&',$params);
            foreach($paramsArr as $k=>$v){
                $a = explode('=',$v);
                $arr[$a[0]] = $a[1];
            }
        }
        
        return $arr;
    }


    public function dispatchAppServer($context) {

        $_ENV = $_SERVER = $context['env'];

        @parse_str($_ENV['QUERY_STRING'], $_GET);
        
        $_GET['ak'] = $_ENV['PATH_INFO'];
        
        Ak::unsetStaticVar('AkRequestSingleton');
        Ak::unsetStaticVar('AkRouterSingleton');
        Ak::unsetStaticVar('AkUrlWriterSingleton');
        AkConfig::setOption('Request.remote_ip', '127.0.0.1');

        try{
            $time_start = microtime(true);
            ENABLE_PROFILER &&  AkDebug::profile(__CLASS__.'::'.__FUNCTION__.'() call');
            $this->Request = AkRequest::getInstance();
            $this->Response = new AkResponse();
            
            $path = ltrim(str_replace('..', '.', $context['env']['REQUEST_URI']), '/. ');
            
            if(empty($path) && file_exists(PUBLIC_DIR.DS.'index.html')){
              $Controller = new AkActionController();
              $Controller->Response = $this->Response;
              $Controller->renderText(file_get_contents(PUBLIC_DIR.DS.'index.html'));
              return $Controller->Response;
            }elseif(!empty($path) && file_exists(PUBLIC_DIR.DS.$path)){
              $Controller = new AkActionController();
              $Controller->Response = $this->Response;
              $Controller->sendFile(PUBLIC_DIR.DS.$path, array('stream'=>false));
              return $Controller->Response;
            }else{
              if($this->Controller = $this->Request->recognize()){
                $this->Controller->ak_time_start = $time_start;
                ENABLE_PROFILER && AkDebug::profile('Request::recognize() completed');
                $this->Controller->process($this->Request, $this->Response);
              }
              return $this->Response;
            }
        }catch(Exception $e){
            if(isset($this->Controller) && method_exists($this->Controller, 'render_error')){
                $this->Controller->render_error($e);
            }else{
                $ExceptionDispatcher = new AkExceptionDispatcher();
                $ExceptionDispatcher->renderException($e);
            }
        }
    }

    public function dispatchCached() {
        $cache_settings = Ak::getSettings('caching', true);
        //~ var_dump($cache_settings);
        if ($cache_settings['enabled']) {
            $null = null;
            $pageCache = new AkCacheHandler();;
            $pageCache->init($null, $cache_settings);
            if (isset($_GET['allow_get'])) {
                $options['include_get_parameters'] = explode(',',$_GET['allow_get']);
            }
            if (isset($_GET['use_if_modified_since'])) {
                $options['use_if_modified_since'] = true;
            }

            if (($cachedPage = $pageCache->getCachedPage()) ) {
                if(is_string($cachedPage)){
                    return include( $cachedPage);
                }else
                    return $cachedPage;//88
                //return $cachedPage->render();
            }
        }
        return false;
    }
    /**
     * @todo Implement a mechanism for enabling multiple requests on the same dispatcher
     * this will allow using Rails as an Application Server using the
     * approach described at http://blog.milkfarmsoft.com/?p=51
     *
     */
    public function restoreRequest() {
    }
}

