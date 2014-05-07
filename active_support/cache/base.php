<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
* Easy to use class for caching data using a database as
* container or the file system.
*
* PhpOnRails Framework provides an easy to use functionality for
* caching data using a database as container or the file
* system.
*
* By default the cache container is defined in the following
* line
*
* <code>define ('CACHE_HANDLER', 1);</code>
*
* in the ''config/config.php'' file
*
* Possible values are:
*
* - 0: No cache at all
* - 1: File based cache using the folder defined at CACHE_DIR or the system /tmp dir
* - 2: Database based cache. This one has a performance penalty, but works on most servers.
*
* Here is a small code spinet of how this works.
* <code>
* // First we create a cache instance
* $Cache = new AkCache();
*
* // Now we define some details for this cache
* $seconds = 3600; // seconds of life for this cache
* $cache_id = 'unique identifier for accesing this cache element';
*
* // Now we call the $Cache constructor (ALA AkFramework)
* $Cache->init($seconds);
*
* // If the data is not cached, we catch it now
* // if it was on cache, $data will hold its content
* if (!$data = $Cache->get($cache_id)) {
* $data = some_heavy_function_that_takes_too_many_time_or_resources();
* $Cache->save($data);
* }
*
* // Now you can use data no matter from where did it came from
* echo $data;
* </code>
*
* This class uses the
* [http://pear.php.net/manual/en/package.caching.cache-lite.php
* pear Cache_Lite] as driver for file based cache.
* In fact you can access an instance of Cache_Lite by
* accesing $Cache->DriverInstance.
*
* @author Bermi Ferrer <bermi at rails dot com>
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
* @since 0.1
* @version $Revision 0.1 $
*/
class AkCache
{

    /**
    * Enables / Disables caching
    */
    public $cache_enabled = true;

    /**
    * Handles an instance of current Cache driver
    */
    public $DriverInstance = NULL;


    /**
     * Instantiates and configures the AkCache store.
     *
     * If $options == NULL the configuration will be taken from the constants:
     *
     * CACHE_HANDLER and CACHE_OPTIONS
     *
     * if $options is of type string/int the $options parameter will be considered
     * as the CACHE_HANDLER_* Type (CACHE_HANDLER_PEAR,CACHE_HANDLER_ADODB,CACHE_HANDLER_MEMCACHE)
     *
     * if $options is an array of format:
     *
     *   array('file'=>array('cacheDir'=>'/tmp'))
     *
     *   or
     *
     *   array(CACHE_HANDLER_PEAR=>array('cacheDir'=>'/tmp'))
     *
     *  the first key will be used as the CACHE_HANDLER_* Type
     *  and the array as the config options
     *
     * Default behaviour is calling the method with the $options == null parameter:
     *
     * AkCache::lookupStore()
     *
     * Calling it with:
     *
     * AkCache::lookupStore(true)
     *
     * will return the configured $cache_store
     *
     * @param mixed $options
     * @return mixed   false if no cache could be configured or AkCache instance
     */
    static function &lookupStore($options = null) {
        static $cache_store;
        $false = false;
        if ($options === true && !empty($cache_store)) {
            return $cache_store;
        } else if (is_array($options) &&
        isset($options['enabled']) && $options['enabled']==true &&
        isset($options['handler']) &&
        isset($options['handler']['type'])) {
            $type = $options['handler']['type'];
            $options = isset($options['handler']['options'])?$options['handler']['options']:array();
        } else if (is_string($options) || is_int($options)) {
            $type = $options;
            $options = array();
        } else {
            return $false;
        }

        $cache_store = new AkCache();
        $cache_store->init($options, $type);
        if ($cache_store->cache_enabled) {
            return $cache_store;
        }
        return $false;
    }

    static function expandCacheKey($key, $namespace = null) {
        $expanded_cache_key = $namespace != null? $namespace : '';
        if (isset($_ENV['CACHE_ID'])) {
            $expanded_cache_key .= DS . $_ENV['CACHE_ID'];
        } else if (isset($_ENV['APP_VERSION'])) {
            $expanded_cache_key .= DS . $_ENV['APP_VERSION'];
        }

        if (is_object($key) && method_exists($key,'cacheKey')) {
            $expanded_cache_key .= DS . $key->cacheKey();
        } else if (is_array($key)) {
            foreach ($key as $idx => $v) {
                $expanded_cache_key .= DS . $idx.'='.$v;
            }
        } else {
            $expanded_cache_key .= DS . $key;
        }
        $regex = '|'.DS.'+|';
        $expanded_cache_key = preg_replace($regex,DS, $expanded_cache_key);
        $expanded_cache_key = rtrim($expanded_cache_key,DS);
        return $expanded_cache_key;
    }

    /**
    * Class constructor (ALA PhpOnRails Framework)
    *
    * This method loads an instance of selected driver in order to
    * use it class wide.
    *
    * @access public
    * @param    mixed    $options    You can pass a number specifying the second for
    * the cache to expire or an array with the
    * following options:
    *
    * <code>
    * $options = array(
    * //This options are valid for both cache contains (database and file based)
    * 'lifeTime' => cache lifetime in seconds
    * (int),
    * 'memoryCaching' => enable / disable memory caching (boolean),
    * 'automaticSerialization' => enable / disable automatic serialization (boolean)
    *
    * //This options are for file based cache
    * 'cacheDir' => directory where to put the cache files (string),
    * 'caching' => enable / disable caching (boolean),
    * 'fileLocking' => enable / disable fileLocking (boolean),
    * 'writeControl' => enable / disable write control (boolean),
    * 'readControl' => enable / disable read control (boolean),
    * 'readControlType' => type of read control
    * 'crc32', 'md5', 'strlen' (string),
    * 'pearErrorMode' => pear error mode (when raiseError is called) (cf PEAR doc) (int),
    * 'onlyMemoryCaching' => enable / disable only memory caching (boolean),
    * 'memoryCachingLimit' => max nbr of records to store into memory caching (int),
    * 'fileNameProtection' => enable / disable automatic file name protection (boolean),
    * 'automaticCleaningFactor' => distable / tune automatic cleaning process (int)
    * 'hashedDirectoryLevel' => level of the hashed directory system (int)
    * );
    * </code>
    * @param    integer    $cache_type    The default value is set by defining the constant CACHE_HANDLER in the following line
    *
    * <code>define ('CACHE_HANDLER', 1);</code>
    *
    * in the ''config/config.php'' file
    *
    * Possible values are:
    *
    * - 0: No cache at all
    * - 1: File based cache using the folder defined at CACHE_DIR or the system /tmp dir
    * - 2: Database based cache. This one has a performance penalty, but works on most servers
    * - 3: Memcached - The fastest option
    * @return void
    */
    public function init($options = null, $cache_type = null) {
        $options = is_int($options) ? array('lifeTime'=>$options) : (is_array($options) ? $options : array());

        switch ($cache_type) {
            case 1:
                $this->cache_enabled = true;
                if(!class_exists('Cache_Lite')){
                    require_once(CONTRIB_DIR.'/pear/Cache_Lite/Lite.php');
                }
                if(!isset($options['cacheDir'])){
                    $options['cacheDir'] = CACHE_DIR.DS;
                } else {
                    $options['cacheDir'].=DS;
                }
                if(!is_dir($options['cacheDir'])){
                    AkFileSystem::make_dir($options['cacheDir'], array('cacheType'=>$cache_type,'base_path'=>dirname($options['cacheDir'])));
                }
                $this->DriverInstance = new Cache_Lite($options);
                break;
            case 2:
                $this->DriverInstance = new AkAdodbCache();
                $res = $this->DriverInstance->init($options);
                $this->DriverInstance->install();
                $this->cache_enabled = $res;
                break;
            case 3:
                   
                        $this->DriverInstance = new AkMemcache();
                        $res = $this->DriverInstance->init($options);
                        $this->cache_enabled = $res;
          
                break;
            default:
                $this->cache_enabled = false;
                break;
        }
    }


    /**
    * Test if a cache is available and (if yes) return it
    *
    * @access public
    * @param    string    $id    Cache id
    * @param    string    $group    Name of the cache group.
    * @return mixed Data of the cache (or false if no cache available)
    */
    public function get($id, $group = 'default') {
        return $this->cache_enabled ? $this->DriverInstance->get($id, $group) : false;
    }


    /**
    * Save some data in the cache
    *
    * @access public
    * @param    string    $data    Data to put in cache
    * @param    string    $id    Cache id
    * @param    string    $group    Name of the cache group
    * @return boolean True if no problem
    */
    public function save($data, $id = null, $group = 'default') {
        return $this->cache_enabled ? $this->DriverInstance->save($data, $id, $group) : true;
    }


    /**
    * Remove a cache item
    *
    * @access public
    * @param    string    $id    Cache id
    * @param    string    $group    Name of the cache group
    * @return boolean True if no problem
    */
    public function remove($id, $group = 'default') {
        return $this->cache_enabled ? $this->DriverInstance->remove($id, $group) : true;
    }


    /**
    * Clean the cache
    *
    * If no group is specified all cache items will be destroyed
    * else only cache items of the specified group will be
    * destroyed
    *
    * @access public
    * @param    string    $group    Name of the cache group.
    * If no group is specified all cache items will be
    * destroyed else only cache items of the specified
    * group will be destroyed
    * @param    string    $mode    Flush cache mode. Options are:
    *
    * - old
    * - ingroup
    * - notingroup
    * @return boolean True if no problem
    */
    public function clean($group = false, $mode = 'ingroup') {
        return $this->cache_enabled ? $this->DriverInstance->clean($group, $mode) : true;
    }

    public function install() {
        return $this->cache_enabled ? $this->DriverInstance->install() : true;
    }

    public function uninstall() {
        return $this->cache_enabled ? $this->DriverInstance->uninstall() : true;
    }

}

