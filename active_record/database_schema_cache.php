<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkDbSchemaCache
{
    static function shouldRefresh($set = null) {
        static $refresh;
        if(!isset($refresh)){
            $refresh = !ACTIVE_RECORD_CACHE_DATABASE_SCHEMA;
        }
        $refresh = is_null($set) ? $refresh : $set;
        return $refresh;
    }

    static function getCacheFileName($environment = ENVIRONMENT) {
        return AkDbSchemaCache::getCacheDir().DS.$environment.'.serialized';
    }

    static function getCacheDir() {
        $cache_dir  = TMP_DIR.DS.'ak_config';
        return $cache_dir.DS.'cache'.DS.'activerecord';
    }

    static function clear($table, $environment = ENVIRONMENT) {
        AkDbSchemaCache::config($table, null, $environment, true);
        AkDbSchemaCache::config('database_table_internals_'.$table, null, $environment, true);
        AkDbSchemaCache::updateCacheFileAfterExecution($environment);
        if(LOG_EVENTS){
            $Logger = Ak::getLogger();
            $Logger->message('Clearing database settings cache for '.$table);
        }
    }

    static function clearAll() {
        if(LOG_EVENTS){
            $Logger = Ak::getLogger();
            $Logger->message('Clearing all database settings from cache');
        }
        AkFileSystem::rmdir_tree(AkDbSchemaCache::getCacheDir());
    }

    static function get($key, $environment = ENVIRONMENT) {
        return AkDbSchemaCache::config($key, null, $environment, false);
    }

    static function set($key, $value, $environment = ENVIRONMENT) {
        AkDbSchemaCache::updateCacheFileAfterExecution($environment);
        return AkDbSchemaCache::config($key, $value, $environment, !is_null($value));
    }

    static function updateCacheFileAfterExecution($environment = null) {
        static $called = false, $_environment;
        if($called == false && !AkDbSchemaCache::shouldRefresh()){
            register_shutdown_function(array('AkDbSchemaCache','updateCacheFileAfterExecution'));
            $called =  !empty($environment) ? $environment : ENVIRONMENT;
        }elseif(empty($environment)){
            $config = AkDbSchemaCache::config(null, null, $called);
            $file_name = AkDbSchemaCache::getCacheFileName($called);

            /**
            * @todo On PHP5 var_export requires objects that implement the __set_state magic method.
            *       As see on stangelanda at arrowquick dot benchmarks at comhttp://php.net/var_export
            *       serialize works faster without opcode caches. We should do our benchmarks with
            *       var_export VS serialize using APC once we fix the __set_state magic on phpAdoDB
            */
            if(LOG_EVENTS){
                $Logger = Ak::getLogger();
            }
            if(!CLI) {
                if(LOG_EVENTS){
                    $Logger->message('Updating database settings on '.$file_name);
                }

                AkFileSystem::file_put_contents($file_name, serialize($config), array('base_path'=> TMP_DIR));
                //file_put_contents($file_name, serialize($config));

            } else if(LOG_EVENTS){
                $Logger->message('Skipping writing of cache file: '.$file_name);
            }
        }
    }

    static function config($key = null, $value = null, $environment = ENVIRONMENT, $unset = false) {
        if(AkDbSchemaCache::shouldRefresh()){
            return false;
        }
        static $config;
        if(!isset($config[$environment])){
            $file_name = AkDbSchemaCache::getCacheFileName($environment);
            $config[$environment] = file_exists($file_name) ? unserialize(file_get_contents($file_name)) : array();
            if(LOG_EVENTS){
                $Logger = Ak::getLogger();
                $Logger->message('Loading cached database settings');
            }
        }
        if(!is_null($key)){
            if(!is_null($value)){
                $config[$environment][$key] = $value;
            }elseif($unset){
                unset($config[$environment][$key]);
            }
            return isset($config[$environment][$key]) ? $config[$environment][$key] : false;
        }
        return $config[$environment];
    }
}

