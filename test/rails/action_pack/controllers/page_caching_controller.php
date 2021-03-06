<?php

class PageCachingController extends ApplicationController
{

    var $caches_page = array('format','index','ok','no_content','found','not_found','simple', 'priority',
                               'skip','formatspecific'=>array('format'=>'js'),'get_parameters'=>array('include_get_parameters'=>array('version')));

    var $caches_action = array('priority');
    
    function ok() {
        
        $this->renderNothing(200);
        
    }
    function formatspecific() {
        if($this->respondToFormat()) return;
        $this->renderText('html format');
    }
    function get_parameters() {
        $this->renderText('version:'.@$this->params['version']);
    }
    function _handleFormatspecificAsJs() {
        $this->renderText('javascript format');
    }
    
    function format() {
        if (!$this->respondToFormat()) {
            $this->renderText('<h1>hello business</h1>');
        }
    }
    
    function _handleFormatAsXml() {
        $this->renderText('<hello>business</hello>');
    }
    
    function _handleFormatAsCsv() {
        $this->renderText('hello,business');
    }
    
    function index() {
        $this->renderText('index');
    }
    function priority() {
        $this->renderText($this->getAppliedCacheType());
    }
    
    function simple() {
        
        $this->renderText('Simple Text');
        
    }
    function expire() {
        $this->expirePage(array('controller'=>'page_caching','action'=>'skip'));
        $this->renderNothing(200);
    }
    
    function skip() {
        $this->renderText('Hello<!--CACHE-SKIP-START-->
        
        You wont see me after the cache is rendered.
        
        <!--CACHE-SKIP-END-->');
    }
    
    function no_content() {
        $this->renderNothing(204);
    }
    function found() {
        $this->redirectToAction('ok');
    }
    
    function not_found() {
        $this->renderNothing(404);
    }
    
    function custom_path() {
        $this->renderText('Rails rulez');
        $this->cachePage('Rails rulez', '/index.html');
    }
    
    function expire_custom_path() {
        $this->expirePage('/index.html');
        $this->renderNothing(200);
    }
    
    function trailing_slash() {
        $this->renderText('Rails');
    }
}