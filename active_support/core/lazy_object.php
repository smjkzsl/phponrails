<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkLazyObject
{
    protected
    $_report_undefined_attributes = true,
    $_extenssionInitMethod = '_enableLazyLoadingExtenssions',
    $_extenssionsInited = false;

    private
    $__extenssionPoints = array(),
    $__extenssionPointOptions = array(),
    $__extendedPoints   = array();

    static function extenssionRegistry($ExtendedClass = null, $ProxyClass = null, $register = null) {
        static $_extenssion_registry = array();

        if(is_null($ExtendedClass) && is_null($ProxyClass)){
            return $_extenssion_registry;
        }
        $extended_name = is_object($ExtendedClass) ? get_class($ExtendedClass) : $ExtendedClass;
        $proxied_name = is_object($ProxyClass) ? get_class($ProxyClass) : $ProxyClass;

        if($register === true){
            $_extenssion_registry[$extended_name][] = $proxied_name;
            return ;
        }
        if($register === false){
		//���ô��󱨸�
		error_reporting(0);

		//��������ʱ����
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		try{
			$_extenssion_registry[$extended_name] = array_diff(array($_extenssion_registry), array($proxied_name));
		}catch(Exception  $e){
			$_extenssion_registry[$extended_name]=array($proxied_name);
		}
		error_reporting(E_ERROR | E_WARNING | E_PARSE);

            return ;
        }
        if($proxied_name === false){ // unregisterExtenssion
            unset($_extenssion_registry[$extended_name]);
        }elseif(empty($proxied_name)){
            return isset($_extenssion_registry[$extended_name]) ? $_extenssion_registry[$extended_name] : array();
        }elseif (empty($extended_name)){
            foreach ($_extenssion_registry as $extended_name => $proxied_names){
                $result = array();
                if(in_array($proxied_name, $proxied_names)){
                    $result[] = $extended_name;
                }
            }
            return $result;
        }
    }

    public function registerExtenssion($extenssion_name) {
        return self::extenssionRegistry($this, $extenssion_name, true);
    }

    public function unregisterExtenssion($extenssion_name) {
        return self::extenssionRegistry($this, $extenssion_name, false);
    }

    public function getExtenssionClasses() {
        return self::extenssionRegistry($this);
    }

    public function getExtendedClasses() {
        return self::extenssionRegistry(null, $this);
    }

    public function isExtending($LazyObject) {
        return in_array(is_object($LazyObject) ? get_class($LazyObject) : $LazyObject, $this->getExtendedClasses());
    }

    public function isExtendedBy($Extenssion) {
        return in_array(is_object($Extenssion) ? get_class($Extenssion) : $Extenssion, $this->getExtenssionClasses());
    }

    public function extendClass(&$ClassToExtend, $options = array()) {
        $class_name = get_class($ClassToExtend);
        if(!array_key_exists($class_name, $this->__extenssionPoints) || !empty($options['force'])){
            $this->__extenssionPoints[$class_name] = $ClassToExtend;
            if(method_exists($this->__extenssionPoints[$class_name], 'setExtendedBy')){
                $this->__extenssionPoints[$class_name]->setExtendedBy($this);
            }
            $this->setExtenssionPointOptions($class_name, $options);
            AkLazyObject::registerExtenssion($class_name);
        }
    }

    public function extendClassLazily($extended_class_name, $options = array()) {
        if(!is_string($extended_class_name)){
            $backtrace = debug_backtrace();
            trigger_error('Fatal error: '.get_class($this).'::extendClassLazily expects a string, '.gettype($extended_class_name).' given in '.$backtrace[0]['file'].' on line '.$backtrace[0]['line'] , E_USER_ERROR);
            return false;
        }
        $this->__extenssionPoints[$extended_class_name] = null;
        $this->setExtenssionPointOptions($extended_class_name, $options);
        AkLazyObject::registerExtenssion($extended_class_name);
    }

    public function setExtenssionPointOptions($extenssion_point, $options = array()) {
        $this->__extenssionPointOptions[$extenssion_point] = $options;

        if(isset($options['autoload_path'])){
            rails_autoload($extenssion_point, $options['autoload_path']);
        }
    }

    public function setExtendedBy(&$ExtendedClass) {
        $class_name = get_class($ExtendedClass);
        $this->__extendedPoints[$class_name] = $ExtendedClass;
        AkLazyObject::extenssionRegistry($ExtendedClass, $class_name, true);
    }

    public function &getInstanceBeingExtended($class_name) {
        if(isset($this->__extendedPoints[$class_name])){
            return $this->__extendedPoints[$class_name];
        }else{
            $backtrace = debug_backtrace();
            trigger_error('Warning: '.get_class($this).'::getInstanceBeingExtended could not find any parent instance in '.$backtrace[0]['file'].' on line '.$backtrace[0]['line'] , E_USER_ERROR);
        }
    }

    public function &getExtendedClassInstance($extended_class_name) {
        $class_name = !is_string($extended_class_name) ? get_class($extended_class_name) : $extended_class_name;
        if(!$this->hasInstantiatedClass($class_name)){
            $this->instantiateExtendedClass($class_name);
        }
        if(isset($this->__extenssionPoints[$class_name])){
            return $this->__extenssionPoints[$class_name];
        }
        trigger_error('Class '.get_class($this).' has not been extended with '.$extended_class_name, E_USER_ERROR);
    }

    public function hasInstantiatedClass($class_name) {
        return  array_key_exists($class_name, $this->__extenssionPoints) &&
        !is_null($this->__extenssionPoints[$class_name]);
    }

    public function &instantiateExtendedClass($class_name) {
        if(!array_key_exists($class_name, $this->__extenssionPoints)){
            $backtrace = debug_backtrace();
            trigger_error('Fatal error: '.get_class($this).'::instantiateExtendedClass() cant instantiate '.$class_name.' because it has not been extended yet in '.$backtrace[0]['file'].' on line '.$backtrace[0]['line'], E_USER_ERROR);
        }
        $this->__extenssionPoints[$class_name] = new $class_name();

        if(
        (method_exists($this, 'beforeBeingExtended') && !$this->beforeBeingExtended($this->__extenssionPoints[$class_name])) ||
        (method_exists($this->__extenssionPoints[$class_name], 'beforeExtending') && !$this->__extenssionPoints[$class_name]->beforeExtending($this))
        ){
            unset($this->__extenssionPoints[$class_name]);
            return false;
        }

        if(isset($this->__extenssionPointOptions[$class_name]['init_method'])){
            $init_method = $this->__extenssionPointOptions[$class_name]['init_method'];
            if(method_exists($this->__extenssionPoints[$class_name], $init_method)){
                $init_options = isset($this->__extenssionPointOptions[$class_name]['init_options']) ? $this->__extenssionPointOptions[$class_name]['init_options'] : $this;
                $this->__extenssionPoints[$class_name]->$init_method($init_options);
            }else{
                trigger_error('Could not find init method '.$init_method.' for Lazy class '.$class_name, E_USER_ERROR);
            }
        }

        if(method_exists($this->__extenssionPoints[$class_name], 'setExtendedBy')){
            $this->__extenssionPoints[$class_name]->setExtendedBy($this);
        }
        if(method_exists($this->__extenssionPoints[$class_name], 'afterExtending')){
            $this->__extenssionPoints[$class_name]->afterExtending($this);
        }
        return $this->__extenssionPoints[$class_name];
    }

    public function extenssionImplements($class_name, $method) {
        return
        ((!empty($this->__extenssionPointOptions[$class_name]['methods'])) && in_array($method, $this->__extenssionPointOptions[$class_name]['methods'])) ||
        (!empty($this->__extenssionPointOptions[$class_name]['methods_match']) && preg_match($this->__extenssionPointOptions[$class_name]['methods_match'], $method));
    }

    public function canRunExtenssionMethod($extenssion_name, $method) {
        if(empty($this->__extenssionPoints[$extenssion_name])){
            return false;
        }
        if(((empty($this->__extenssionPointOptions[$extenssion_name]['methods']) && empty($this->__extenssionPointOptions[$extenssion_name]['methods_match'])) ||
        (!empty($this->__extenssionPointOptions[$extenssion_name]['methods']) || !empty($this->__extenssionPointOptions[$extenssion_name]['methods_match'])) && $this->extenssionImplements($extenssion_name, $method))){
            return !empty($this->__extenssionPointOptions[$extenssion_name]['accept_all_matches']) || method_exists($this->__extenssionPoints[$extenssion_name], $method);
        }
        return false;
    }
    
    private function _enableExtenssionsIfRequired(){
        if(!$this->_extenssionsInited){
            $this->_extenssionsInited = true;
            if(method_exists($this, $this->_extenssionInitMethod)){
                $this->{$this->_extenssionInitMethod}();
            }
        }
    }
    
    public function __get($name) {
        if($name[0] != '_'){
            $this->_enableExtenssionsIfRequired();
            foreach ($this->__extenssionPoints as $extenssion_name => $ExtenssionPoint){
                $has_implicit_attributes = !empty($this->__extenssionPointOptions[$extenssion_name]['attributes']);
                if(!$has_implicit_attributes && isset($ExtenssionPoint->$name)){
                    $this->$name =& $ExtenssionPoint->$name;
                    break;
                }else{
                    if($has_implicit_attributes){
                        if(in_array($name, $this->__extenssionPointOptions[$extenssion_name]['attributes'])){

                            if(is_null($ExtenssionPoint)){
                                $ExtenssionPoint = $this->instantiateExtendedClass($extenssion_name);
                            }
                            if(isset($ExtenssionPoint->$name)){
                                $this->$name =& $ExtenssionPoint->$name;
                                break;
                            }
                        }
                    }
                }
            }
            if(isset($this->$name)){
                return $this->$name;
            }
        }
        if(!$this->__getFailOver($name) && $this->_report_undefined_attributes){
            $backtrace = debug_backtrace();
            trigger_error("Notice: Call to undefined attribute ".get_class($this)."::".$name.' in '.$backtrace[0]['file'].' on line '.$backtrace[0]['line'], E_USER_NOTICE);
        }

        if(isset($this->$name)){
            return $this->$name;
        }
    }
    
    protected function __getFailOver($name){
        return false;
    }

    public function __call($name, $attributes = array()) {
        /*
        static $methods;
        if(empty($methods)){
        $methods = true;
        register_shutdown_function('show_most_used_methods');
        }
        $stats = (array)@Ak::getStaticVar('stats');
        @$stats[$name]++;
        Ak::setStaticVar('stats', $stats);
        */

        if($name[0] != '_'){
            static $handlers = array();

            $this->_enableExtenssionsIfRequired();
            
            if(isset($handlers[$name])){

                if(!$this->isExtendedBy($handlers[$name])){
                    unset($handlers[$name]);
                }else{
                    $extenssion_name = $handlers[$name];
                    if(array_key_exists($extenssion_name, $this->__extenssionPoints)){
                        if(is_null($this->__extenssionPoints[$extenssion_name]) && $this->extenssionImplements($extenssion_name, $name)){
                            $this->instantiateExtendedClass($extenssion_name);
                        }
                        if($this->canRunExtenssionMethod($extenssion_name, $name)){
                            return call_user_func_array(array($this->__extenssionPoints[$extenssion_name], $name), $attributes);
                        }
                    }
                }
            }
            foreach ($this->__extenssionPoints as $extenssion_name => $ExtenssionPoint){
                if(is_null($ExtenssionPoint) && $this->extenssionImplements($extenssion_name, $name)){
                    $ExtenssionPoint = $this->instantiateExtendedClass($extenssion_name);
                }
                if($this->canRunExtenssionMethod($extenssion_name, $name)){
                    $handlers[$name] = $extenssion_name;
                    return call_user_func_array(array($ExtenssionPoint, $name), $attributes);
                }
            }
        }
        if($result = $this->__callFailOver($name, $attributes)){
            return $result;
        }
        $backtrace = debug_backtrace();
        trigger_error("Fatal error: Call to undefined method ".get_class($this)."::".$name.'() in '.@$backtrace[1]['file'].' on line '.@$backtrace[1]['line'], E_USER_ERROR);
    }
    
    protected function __callFailOver($name, $attributes = array()){
        return false;
    }
}

/*
function show_most_used_methods() {
$stats = (array)@Ak::getStaticVar('stats');
arsort($stats);
var_export($stats);
}
*/
