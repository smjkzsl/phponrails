<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details
//~ $mmc=memcache_init();
//~ if($mmc==false)
    //~ echo "mc init failed\n";
//~ else
//~ {
    //~ memcache_set($mmc,"key","value");
    //~ echo memcache_get($mmc,"key");
//~ }

if(!IN_SAE)
    include_once(CONTRIB_DIR.DS.'phpmemcached'.DS.'class_MemCachedClient.php');

class AkMemcache
{
    /**
     * @protected MemCachedClient
     */
    protected $_memcache;
    /**
     * caching the integer namespace values
     *
     * @protected array
     */
    protected $_namespaces = array();

    /**
     * max storable size for 1 key,
     * above this size, the class will autosplit
     * the data into chunks
     *
     * @protected int
     */
    protected $_max_size = 1000000;

    protected $_servers = array();
    protected $_lifeTime = 0;

    public function init($options = array()) {
        if(IN_SAE){
            
            return $this->_memcache=memcache_init();
        }
        
        
        $default_options = array('servers'=>array('localhost:11211'),'lifeTime'=>0);
        $options = array_merge($default_options, $options);

        $this->_lifeTime = $options['lifeTime'];
        if (empty($options['servers'])) {
            trigger_error('Need to provide at least 1 server',E_USER_ERROR);
            return false;
        }
        $this->_memcache = new MemCachedClient(is_array($options['servers']) ? $options['servers'] : array($options['servers']));

        $ping = $this->_memcache->get('ping');
        if (!$ping) {
            if ($this->_memcache->errno==ERR_NO_SOCKET) {
                if(empty($options['silent_mode'])){
                    trigger_error("Could not connect to MemCache daemon. ".AkDebug::getFileAndNumberTextForError(1), E_USER_WARNING);
                }
                return false;
            }
            $this->_memcache->set('ping',1);
        }
        return true;
    }


    protected function _getNamespaceId($group) {
        $ident = $group;
        return $ident;
    }

    protected function _clearNamespace($group) {
        $group = 'group_'.md5($group);
        $ident = $this->_getNamespaceId($group);
        unset($this->_namespaces[$group]);
        return $this->_memcache->incr($ident,1);
    }

    protected function _getNamespace($group) {
        $groupName = $group;
        $group = 'group_'.md5($groupName);
        if (!isset($this->_namespaces[$group])) {
            $ident = $this->_getNamespaceId($group);
            if(IN_SAE){
                $namespaceVersion = memcache_get($this->_memcache,$ident);
            }else{
                $namespaceVersion = $this->_memcache->get($ident);
            }
            if (!$namespaceVersion) {
                if (!$this->_memcache || (!IN_SAE && $this->_memcache->errno==ERR_NO_SOCKET)) {
                    trigger_error("Could not connect to MemCache daemon", E_USER_ERROR);
                }
                $namespaceVersion = 1;
                if(IN_SAE){
                    memcache_set($this->_memcache,$ident,$namespaceVersion);
                }else{
                    $this->_memcache->set($ident,$namespaceVersion);
                }

            }
            $this->_namespaces[$group] = $groupName.'_'.$namespaceVersion;
        }
        return $this->_namespaces[$group];
    }

    protected function _generateCacheKey($id,$group) {
        $namespace = $this->_getNamespace($group);
        $key = $namespace.'_'.$id;
        $key = 'key_'.md5($key);
        return $key;
    }

    public function get($id, $group = 'default') {
        $key = $this->_generateCacheKey($id, $group);
        if(IN_SAE){
            $return=memcache_get($this->_memcache,$key);
        }else{
            $return = $this->_memcache->get($key);
        }

        if ($return === false) {
            return false;
        }

        if (is_string($return) && strstr($return, '@#!')) {
            $parts = explode('@#!', $return, 2); // 0 type, 1 data
            settype($parts[1], $parts[0]);
            return $parts[1];
        }
        //将超过长度限制的分段字符串合     
        if (is_string($return) && substr($return,0,15) == '@____join____@:') {
            list(, $parts) = explode(':', $return, 2);
            $return = '';
            for($i=0;$i<(int)$parts;$i++) {
                if(IN_SAE){
                    $return.=memcache_get($this->_memcache,$key.'_'.$i);
                }else{
                    $return.=$this->_memcache->get($key.'_'.$i);
                }
            }
        }
        return $return;
    }
    //set
    //'@#!' 的数据为字或尔型
    //'@____join____@:' 分段数据，为段 
    public function save($data, $id = null, $group = null) {
        $key = $this->_generateCacheKey($id, $group);
       
        if (is_numeric($data) || is_bool($data)) {
            $type=gettype($data);
            $data = $type.'@#!'.$data;
        } else if (is_string($data) && ($strlen=strlen($data))> $this->_max_size) {
            $parts = round($strlen / $this->_max_size);
            
            for ($i=0;$i<$parts;$i++) {//分段保存过长度的数据
                $nkey = $key.'_'.$i;
                $tmpData=substr($data,$i*$this->_max_size,$this->_max_size);
                if(IN_SAE){
                    memcache_set($this->_memcache,$nkey,$tmpData);
                }else{
                    $this->_memcache->set($nkey,$tmpData,$this->_lifeTime);
                }
            }
            if(IN_SAE){
                $return=memcache_set($this->_memcache,$key,'@____join____@:');
            }else{
                $return = $this->_memcache->set($key,'@____join____@:'. $parts);
            }
            return $return !== false ? true:false;
        }
        //数字或布尔型
        if(IN_SAE){
             return memcache_set($this->_memcache,$key,$data);
        }else{
            $return = $this->_memcache->set($key,$data, $this->_lifeTime);
        }
        return $return !== false ? true:false;
    }

    public function remove($id, $group = 'default') {
        //~ if(IN_SAE)return false;
        $key = $this->_generateCacheKey($id, $group);
        $return = $this->_memcache->delete($key);
        return $return;
    }

    public function clean($group = false, $mode = 'ingroup') {
        switch ($mode) {
            case 'ingroup':
                return $this->_clearNamespace($group);
            case 'notingroup':
                return false;
            case 'old':
                return true;
            default:
                return true;
        }
    }

    static function isServerUp($options = array()) {
        $options['silent_mode'] = true;
        $Memcached = new AkMemcache();
        return $Memcached->init($options) != false;
    }

    public function install(){}
    public function uninstall(){}
}
