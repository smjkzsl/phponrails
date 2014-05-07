<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkResponse
{
    public $_resutl_stack = array();
    public $_headers = array(
    'X-Powered-By' => 'Rails',
    );
    public $_headers_sent = array();
    public $body = '';
    public $__Logger;

    public $_output_flushed = false;

    public $_default_status = 200;

    public function setBody($body){
        $this->body = $body;
    }

    public function &getBody(){
        return $this->body;
    }

    public function set($data, $id = null) {
        if(isset($id)){
            $this->_resutl_stack[$id] = $data;
        }else{
            $this->_resutl_stack[] = $data;
        }
    }

    public function &get($id) {
        if(isset($this->_resutl_stack[$id])){
            return $this->_resutl_stack[$id];
        }
        return false;
    }

    public function getAll() {
        return $this->_resutl_stack;
    }

    public function addHeader() {
        $args = func_get_args();
        if(isset($args[1])){
            $this->_headers[$args[0]] = $args[1];
        }elseif (isset($args[0]) && is_array($args[0])){
            $this->_headers = array_merge($this->_headers,$args[0]);
        }elseif (isset($args[0])){
            $this->_headers[] = $args[0];
        }
    }

    public function addHeaders(){
        $args = func_get_args();
        call_user_func_array(array($this, 'addHeader'), $args);
    }

    public function setContentTypeForFormat($format) {
        if (!empty($format)) {
            $mime_type = Ak::mime_content_type('file.'.$format);
            if (!empty($mime_type)) {
                $this->addHeader('Content-Type', $mime_type);
            }
        }
    }

    public function outputResults() {
        $this->sendHeaders();
        if($this->_streamBody()){
            LOG_EVENTS && !empty($this->_Logger) ? $this->_Logger->message("Sending response as stream") : null;
            $this->body->stream();
        }else{
            LOG_EVENTS && !empty($this->_Logger) ? $this->_Logger->message("Sending response") : null;
            Ak::puts($this->body);
        }
    }

    public function getContentType() {
        return $this->getHeader('Content-Type', $this->_default_status);
    }

    public function getLocation() {
        return $this->getHeader('Location', $this->_default_status);
    }

    public function getStatus() {
        return $this->getHeader('Status', $this->_default_status);
    }

    public function setStatus($status) {
        $this->_headers['Status'] = $status;
    }

    public function getHeaders() {
        return $this->_headers;
    }

    public function getHeader($name, $default = null) {
        return isset($this->_headers[$name])?$this->_headers[$name]:$default;
    }

    public function sendHeaders($terminate_if_redirected = true) {
        /**
        * Fix a problem with IE 6.0 on opening downloaded files:
        * If Cache-Control: IE removes the file it just downloaded from
        * its cache immediately
        * after it displays the "open/save" dialog, which means that if you
        * hit "open" the file isn't there anymore when the application that
        * is called for handling the download is run, so let's workaround that
        */
        if(isset($this->_headers['Cache-Control']) && $this->_headers['Cache-Control'] == 'no-cache'){
            $this->_headers['Cache-Control'] = 'private';
        }
        if (empty($this->_headers['Status'])) {
            $this->_headers['Status'] = $this->_default_status;
        }

        $status = $this->getStatusHeader($this->_headers['Status']);
        array_unshift($this->_headers,  $status ? $status : (strstr('HTTP/1.1 '.$this->_headers['Status'],'HTTP') ? $this->_headers['Status'] : 'HTTP/1.1 '.$this->_headers['Status']));

        if(is_numeric($this->_headers['Status'])){
          $this->_headers['Status'] = str_replace('HTTP/1.1 ','', $status);
        }

        $_has_content_type = $_has_content_length = false;
        $_can_add_charset = defined('CHARSET');
        if(!empty($this->_headers) && is_array($this->_headers)){
            $this->addHeader('Connection', 'close');
            foreach ($this->_headers as $k=>$v){
                $header = trim((!is_numeric($k) ? $k.': ' : '').$v);
                $this->_headers_sent[] = $header;
                if(strtolower(substr($header,0,9)) == 'location:'){
                    $header = str_replace(array("\n","\r"), '', $header);
                    $_redirected = true;
                }
                $lowercase_header = strtolower($header);
                if(!$_has_content_type && substr($lowercase_header,0,13) == 'content-type:'){
                    if(!strstr($lowercase_header,'charset') && $_can_add_charset && (empty($_redirected) || (!empty($_redirected) && !empty($javascript_redirection)))){
                        $header = $header.'; charset='.CHARSET;
                    }
                    $_has_content_type = true;
                }elseif(!$_has_content_length && substr($lowercase_header,0,15) == 'content-length:'){
                    $_has_content_length = true;
                }

                LOG_EVENTS && !empty($this->_Logger) ? $this->_Logger->message("Sending header:  $header") : null;
                Ak::header($header);
            }
        }
        $_can_add_charset = !$_can_add_charset ? false : (empty($_redirected) || (!empty($_redirected) && !empty($javascript_redirection)));
        if(!$_has_content_type && $_can_add_charset){
            Ak::header('Content-Type: text/html; charset='.CHARSET);
            $this->_headers_sent[] = 'Content-Type: text/html; charset='.CHARSET;
        }

        if(!$_has_content_length && !$this->_streamBody()){
            $length = !in_array('Content-Encoding: gzip', headers_list()) ? strlen($this->body) : mb_strlen($this->body,'latin1');
            if($length > 0){
                Ak::header('Content-Length: '.$length);
                $this->_headers_sent[] = 'Content-Length: '.$length;
            }
        }

        if(!empty($javascript_redirection)){
            Ak::puts($javascript_redirection);
        }

        $terminate_if_redirected ? (!empty($_redirected) ? exit() : null) : null;
    }

    public function addSentHeader($header) {
        $this->_headers_sent[] = $header;
    }

    public function deleteHeader($header) {
        unset($this->_headers[$header]);
    }

    /**
    * Redirects to given $url, after turning off $this->autoRender.
    *
    * @param unknown_type $url
    */
    public function redirect ($url) {
        $this->autoRender = false;
        if(!empty($this->_headers['Status']) && substr($this->_headers['Status'],0,3) != '301'){
            $this->_headers['Status'] = 302;
        }
        $this->addHeader('Location', $url);
        $this->sendHeaders();
    }


    static function getStatusHeader($status_code) {
        $status_codes = array (
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
        );
        return empty($status_codes[$status_code]) ? false : $status_codes[$status_code];
    }

    public function getStatusDescription($status_code){
        return str_replace('HTTP/1.1 ','', $this->getStatusHeader($status_code));
    }

    public function _streamBody() {
        return is_object($this->body) && method_exists($this->body,'stream');
    }
}

