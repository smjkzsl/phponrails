<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkHttpClient
{
    public $HttpRequest;
    public $error;
    public $Response;
    private $_cookie_path;
    private $_cookie_jar = 'default';

    public function get($url, $options = array()) {
        return $this->customRequest($url, 'GET', $options);
    }

    public function post($url, $options = array(), $body = '') {
        return $this->customRequest($url, 'POST', $options, $body);
    }

    public function put($url, $options = array(), $body = '') {
        return $this->customRequest($url, 'PUT', $options, $body);
    }

    public function delete($url, $options = array()) {
        return $this->customRequest($url, 'DELETE', $options);
    }

    // prefix_options, query_options = split_options(options)

    public function customRequest($url, $http_verb = 'GET', $options = array(), $body = '') {
        $this->getRequestInstance($url, $http_verb, $options, $body);
        return empty($options['cache']) ? $this->sendRequest() : $this->returnCustomRequestFromCache($url,$options);
    }

    public function returnCustomRequestFromCache($url, $options) {
        $Cache = Ak::cache();
        $Cache->init(is_numeric($options['cache']) ? $options['cache'] : 86400, !isset($options['cache_type']) ? 1 : $options['cache_type']);
        if (!$data = $Cache->get('AkHttpClient_'.md5($url))) {
            $data = $this->sendRequest();
            $Cache->save($data);
        }
        return $data;
    }

    public function urlExists($url) {
        $this->getRequestInstance($url, 'GET');
        $this->sendRequest(false);
        return $this->code == 200;
    }

    public function getRequestInstance($url, $http_verb = 'GET', $options = array(), $body = '') {
        $default_options = array(
        'header' => array(),
        'params' => array(),
        );

        $options = array_merge($default_options, $options);

        $options['header']['user-agent'] = empty($options['header']['user-agent']) ?
        'Rails PHP Framework AkHttpClient (http://rails.org)' : $options['header']['user-agent'];

        list($user_name, $password) = $this->_extractUserNameAndPasswordFromUrl($url);

        require_once(CONTRIB_DIR.DS.'pear'.DS.'HTTP'.DS.'Request.php');

        $this->{'_setParamsFor'.ucfirst(strtolower($http_verb))}($url, $options['params']);

        $this->HttpRequest = new HTTP_Request($url);

        $user_name ? $this->HttpRequest->setBasicAuth($user_name, $password) : null;

        $this->HttpRequest->setMethod(constant('HTTP_REQUEST_METHOD_'.$http_verb));

        if ($http_verb == 'PUT' && !empty($options['params'])){
            $this->setBody(http_build_query($options['params']));
        }
        if (!empty($body)){
            $this->setBody($body);
        }
        if (!empty($options['file'])){
            $this->HttpRequest->addFile($options['file']['inputname'],$options['file']['filename']);
        }

        !empty($options['params']) && $this->addParams($options['params']);

        isset($options['cookies']) &&  $this->addCookieHeader($options, $url);

        $this->addHeaders($options['header']);

        return $this->HttpRequest;
    }

    public function addHeaders($headers) {
        foreach ($headers as $k=>$v){
            $this->addHeader($k, $v);
        }
    }

    public function addHeader($name, $value) {
        $this->HttpRequest->removeHeader($name);
        $this->HttpRequest->addHeader($name, $value);
    }

    public function getResponseHeader($name) {
        return $this->HttpRequest->getResponseHeader($name);
    }

    public function getResponseHeaders() {
        return $this->HttpRequest->getResponseHeader();
    }

    public function getResponseCode() {
        return $this->HttpRequest->getResponseCode();
    }

    public function addParams($params = array()) {
        if(!empty($params)){
            foreach (array_keys($params) as $k){
                $this->HttpRequest->addPostData($k, $params[$k]);
            }
        }
    }

    public function setBody($body) {
        $this->HttpRequest->setBody($body);
    }

    public function sendRequest($return_body = true) {
        $this->Response = $this->HttpRequest->sendRequest();
        $this->code = $this->HttpRequest->getResponseCode();
        $this->persistCookies();
        if (PEAR::isError($this->Response)) {
            $this->error = $this->Response->getMessage();
            trigger_error($this->error, E_USER_NOTICE);
            return false;
        } else {
            return $return_body ? $this->HttpRequest->getResponseBody() : true;
        }
    }

    public function addCookieHeader(&$options, $url) {
        if(isset($options['cookies'])){
            $url_details = parse_url($url);
            $jar = Ak::sanitize_include((empty($options['jar']) ? $this->_cookie_jar : $options['jar']), 'paranoid');

            $this->setCookiePath('cookies'.DS.$jar.DS.Ak::sanitize_include($url_details['host'],'paranoid'));
            if($options['cookies'] === false){
                $this->deletePersistedCookie();
                return;
            }
            if($cookie_value = $this->getPersistedCookie()){
                $this->_persisted_cookie = $cookie_value;
                $options['header']['cookie'] = $cookie_value;
            }
        }
    }

    public function setCookiePath($path) {
        $this->_cookie_path = $path;
    }

    public function getPersistedCookie() {
        if(file_exists(AkConfig::getDir('tmp').DS.$this->_cookie_path)){
            return AkFileSystem::file_get_contents($this->_cookie_path, array('base_path' => AkConfig::getDir('tmp')));
        }
        return false;
    }

    public function deletePersistedCookie() {
        $path = $this->_cookie_path;
        $this->_cookie_path = false;
        if(file_exists(AkConfig::getDir('tmp').DS.$path)){
            AkFileSystem::file_delete($path, array('base_path' => AkConfig::getDir('tmp')));
            return;
        }
        return false;
    }

    public function persistCookies() {
        if($this->_cookie_path){
            $cookies_from_response = $this->HttpRequest->getResponseCookies();
            if(!empty($this->_persisted_cookie)){
                $this->HttpRequest->_cookies = array();
                $persisted_cookies = $this->HttpRequest->_response->_parseCookie($this->_persisted_cookie);
                $this->HttpRequest->_cookies = $cookies_from_response;
            }
            if(!empty($cookies_from_response)){
                $all_cookies = array_merge(isset($persisted_cookies)?$persisted_cookies:array(), $cookies_from_response);
                $cookies = array();
                foreach($all_cookies as $cookie){
                    if(!empty($cookie['value'])){
                        $cookies[$cookie['name']] = "{$cookie['name']}={$cookie['value']}";
                    }
                }
                $cookie_string = trim(join($cookies, '; '));
                AkFileSystem::file_put_contents($this->_cookie_path, $cookie_string, array('base_path' => AkConfig::getDir('tmp')));
            }
        }
    }

    protected function _extractUserNameAndPasswordFromUrl(&$url) {
        return array(null,null);
    }

    public function getParamsOnUrl($url) {
        $parts = parse_url($url);
        if($_tmp = (empty($parts['query']) ? false : $parts['query'])){
            unset($parts['query']);
            $url = $this->_httpRenderQuery($parts);
        }
        $result = array();
        !empty($_tmp) && parse_str($_tmp, $result);
        return $result;
    }

    public function getUrlWithParams($url, $params) {
        $parts = parse_url($url);
        $parts['query'] = http_build_query($params);
        return $this->_httpRenderQuery($parts);
    }

    protected function _setParamsForGet(&$url, &$params) {
        $url_params = $this->getParamsOnUrl($url);
        if(!(!count($url_params) && !empty($params))){
            $params = array_merge($url_params, $params);
        }
        $url = $this->getUrlWithParams($url, $params);
    }

    protected function _setParamsForPost(&$url, &$params) {
        empty($params) && $params = $this->getParamsOnUrl($url);
    }

    protected function _setParamsForPut(&$url, &$params) {
        empty($params) && $params = $this->getParamsOnUrl($url);
    }

    protected function _setParamsForDelete(&$url, &$params) {
        $this->_setParamsForGet($url, $params);
    }

    protected function _httpRenderQuery($parts) {
        return is_array($parts) ? (
        (isset($parts['scheme']) ? $parts['scheme'].':'.((strtolower($parts['scheme']) == 'mailto') ? '' : '//') : '').
        (isset($parts['user']) ? $parts['user'].(isset($parts['pass']) ? ':'.$parts['pass'] : '').'@' : '').
        (isset($parts['host']) ? $parts['host'] : '').
        (isset($parts['port']) ? ':'.$parts['port'] : '').
        (isset($parts['path'])?((substr($parts['path'], 0, 1) == '/') ? $parts['path'] : ('/'.$parts['path'])):'').
        (isset($parts['query']) ? '?'.$parts['query'] : '').
        (isset($parts['fragment']) ? '#'.$parts['fragment'] : '')
        ) : false;
    }
}

