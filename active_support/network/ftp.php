<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

defined('AUTOMATIC_CONFIG_VARS_ENCRYPTION')  || define('AUTOMATIC_CONFIG_VARS_ENCRYPTION', false);
defined('FTP_SHOW_ERRORS')                   || define('FTP_SHOW_ERRORS', true);

class AkFtp
{
    static function put_contents ($file, $contents) {
        $result = false;

        if($ftp = AkFtp::connect()){

            $file = AkFtp::unrelativizePath($file);
            $file = str_replace('\\','/',$file);

            $path = dirname($file);

            if(!AkFtp::is_dir($path)){
                AkFtp::make_dir($path);
            }

            $tmpfname = tempnam('/tmp', 'tmp');

            $temp = fopen($tmpfname, 'a+');
            fwrite($temp, $contents);
            fclose($temp);

            $temp = fopen($tmpfname, 'rb');
            $result = ftp_fput($ftp, $file , $temp, FTP_BINARY);

            fclose($temp);
            unlink($tmpfname);
        }

        return $result;
    }


    static function get_contents ($file) {
        if($ftp = AkFtp::connect()){

            $file = AkFtp::unrelativizePath($file);
            $file = str_replace('\\','/',$file);

            $tmpfname = tempnam('/tmp', 'tmp');

            ftp_get($ftp, $tmpfname, $file , FTP_BINARY);

            $file_contents = @file_get_contents($tmpfname);

            unlink($tmpfname);

            return $file_contents;
        }
    }

    static function connect($base_dir = null) {
        static $ftp_conn, $_base_dir, $disconnected = false;

        if(!isset($ftp_conn) || $disconnected){
            if(!defined('FTP_PATH')){
                trigger_error(Ak::t('You must set a valid FTP connection on FTP_PATH in your config/config.php file'),E_USER_ERROR);
            }else {
                if(AUTOMATIC_CONFIG_VARS_ENCRYPTION && substr(FTP_PATH,0,10) == 'PROTECTED:'){
                    // You should change the key bellow and encode this file if you are going to distribute applications
                    // The ideal protection would be to encode user configuration file.
                    $FTP_PATH = Ak::decrypt(base64_decode(substr(FTP_PATH,10)),'HR23JHR93JZ0ALi1UvTZ0ALi1UvTk7MD70');
                    $_pass_encoded = true;
                }else{
                    $FTP_PATH = FTP_PATH;
                }
                $f = parse_url($FTP_PATH);
                if(@$f['scheme'] != 'ftps'){
                    $ftp_conn = isset($f['port']) ?  ftp_connect($f['host'], $f['port']) : ftp_connect($f['host']);
                }else{
                    $ftp_conn = isset($f['port']) ?  ftp_ssl_connect($f['host'], $f['port']) : ftp_ssl_connect($f['host']);
                }

                $f['user'] = str_replace('+','@', @$f['user']);
                $login_result = ftp_login($ftp_conn, $f['user'], @$f['pass']);

                if(!$ftp_conn || !$login_result){
                    FTP_SHOW_ERRORS ? trigger_error(Ak::t('Could not connect to the FTP server'), E_USER_NOTICE) : null;
                    return false;
                }

                $_base_dir = isset($f['path']) ? '/'.trim($f['path'],'/') : '/';

                if(defined('FTP_AUTO_DISCONNECT') && FTP_AUTO_DISCONNECT){
                    register_shutdown_function(array('AkFtp', 'disconnect'));
                }
                if(AUTOMATIC_CONFIG_VARS_ENCRYPTION && empty($_pass_encoded)){

                    @register_shutdown_function(create_function('',
                    "@AkFileSystem::file_put_contents(AkConfig::getDir('config').DS.'config.php',
                str_replace(FTP_PATH,'PROTECTED:'.base64_encode(Ak::encrypt(FTP_PATH,'HR23JHR93JZ0ALi1UvTZ0ALi1UvTk7MD70')),
                AkFileSystem::file_get_contents(AkConfig::getDir('config').DS.'config.php')));"));
                }
            }
        }

        if(isset($base_dir) && $base_dir === 'DISCONNECT_FTP'){
            $disconnected = true;
            $base_dir = null;
        }else {
            $disconnected = false;
        }


        if(!isset($base_dir) && isset($_base_dir) && ('/'.trim(ftp_pwd($ftp_conn),'/') != $_base_dir)){
            if (!@ftp_chdir($ftp_conn, $_base_dir) && FTP_SHOW_ERRORS) {
                trigger_error(Ak::t('Could not change to the FTP base directory %directory',array('%directory'=>$_base_dir)),E_USER_NOTICE);
            }
        }elseif (isset($base_dir)){
            if (!ftp_chdir($ftp_conn, $base_dir) && FTP_SHOW_ERRORS) {
                trigger_error(Ak::t('Could not change to the FTP directory %directory',array('%directory'=>$base_dir)),E_USER_NOTICE);
            }
        }

        return $ftp_conn;
    }

    static function disconnect() {
        static $disconnected = false;
        if(!$disconnected && $ftp_conn = AkFtp::connect('DISCONNECT_FTP')){
            $disconnected = ftp_close($ftp_conn);
            return $disconnected;
        }
        return false;
    }

    static function make_dir($path) {
        if($ftp_conn = AkFtp::connect()){
            $path = AkFtp::unrelativizePath($path);
            $path = str_replace('\\','/',$path);
            if(!strstr($path,'/')){
                $dir = array(trim($path,'.'));
            }else{
                $dir = (array)@explode('/', trim($path,'/.'));
            }
            $path = ftp_pwd($ftp_conn).'/';
            $ret = true;

            for ($i=0; $i<count($dir); $i++){
                $path .= $i === 0 ? $dir[$i] : '/'.$dir[$i];
                if(!@ftp_chdir($ftp_conn, $path)){
                    $ftp_conn = AkFtp::connect();
                    if(ftp_mkdir($ftp_conn, $path)){
                        if (defined('FTP_DEFAULT_DIR_MOD')){
                            if(!ftp_site($ftp_conn, "CHMOD ".FTP_DEFAULT_DIR_MOD." $path")){
                                trigger_error(Ak::t('Could not set default mode for the FTP created directory %path',array('%path',$path)), E_USER_NOTICE);
                            }
                        }
                    }else {
                        $ret = false;
                        break;
                    }
                }
            }
            return $ret;
        }
        return false;
    }

    static function delete($path, $only_files = false) {
        $result = false;
        if($ftp_conn = AkFtp::connect()){
            $path = AkFtp::unrelativizePath($path);
            $path = str_replace('\\','/',$path);
            $path = str_replace(array('..','./'),array('',''),$path);
            $keep_parent_dir = substr($path,-2) != '/*';
            $path = trim($path,'/*');
            $list = FTP_SHOW_ERRORS ? ftp_rawlist ($ftp_conn, "-R $path") : @ftp_rawlist ($ftp_conn, "-R $path");
            $dirs = $keep_parent_dir ? array($path) : array();
            $files = array($path);
            $current_dir = $path.'/';
            if(count($list) === 1 && !AkFtp::is_dir($path)){
                $dirs = array();
                $files[] = $path;
            }else{
                foreach ($list as $k=>$line){
                    if(substr($line,-1) == ':'){
                        $current_dir = substr($line,0,strlen($line)-1).'/';
                    }
                    if (preg_match("/([-d][rwxst-]+).* ([0-9]) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9]) ([0-9]{2}:[0-9]{2}) (.+)/", $line, $regs)){
                        if((substr ($regs[1],0,1) == "d")){
                            if($regs[8] != '.' && $regs[8] != '..'){
                                $dirs[] = $current_dir.$regs[8];
                            }
                        }else {
                            $files[] = $current_dir.$regs[8];
                        }
                    }
                }
            }
            if(count($files) >= 1){
                array_shift($files);
            }
            rsort($dirs);
            foreach ($files as $file){
                if(!($result = @ftp_delete($ftp_conn, $file))){
                    trigger_error(Ak::t('Could not delete FTP file %file_path',array('%file_path'=>$file)), E_USER_NOTICE);
                    return false;
                }
            }
            if(!$only_files){
                foreach ($dirs as $dir){
                    if(!$result = @ftp_rmdir($ftp_conn,$dir)){
                        trigger_error(Ak::t('Could not delete FTP directory %dir_path',array('%dir_path'=>$dir)), E_USER_NOTICE);
                        return false;
                    }
                }
            }
        }
        return $result;
    }


    static function is_dir($path) {
        if($ftp_conn = AkFtp::connect()){
            $path = AkFtp::unrelativizePath($path);
            $path = str_replace('\\','/',$path);
            $result = @ftp_chdir ($ftp_conn, $path);
            AkFtp::connect();
            return $result;
        }
        return false;
    }

    static function unrelativizePath($path) {

        if(!strstr($path,'..')){
            return $path;
        }

        $path_parts = explode(DS, $path);
        if(!empty($path_parts)){
            $new_parts = array();
            for ($i = 0, $count = sizeof($path_parts); $i < $count; $i++) {
                if ($path_parts[$i] === '' || $path_parts[$i] == '.'){
                    continue;
                }
                if ($path_parts[$i] == '..') {
                    array_pop($new_parts);
                    continue;
                }
                array_push($new_parts, $path_parts[$i]);
            }
            return implode(DS, $new_parts);
        }
        return false;
    }
}

