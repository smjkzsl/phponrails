<?php

class FrameworkSetup
{
    public $available_databases = array(
    'mysql' => 'MySQL',
    'pgsql' => 'PostgreSQL',
    'sqlite' => 'SQLite'
    );
    public $available_locales = array('en', 'cn');
    public $locales = array('cn');

    public $stylesheets = array('scaffold','forms');

    public function __construct() {
        if(file_exists(CONFIG_DIR.DS.'config.php')){
            echo Ak::t('We found that you already have a configuration file at config/config.php. You need to remove that file first in order to run the setup.', array(), 'framework_setup');
            die();
        }
    }

    /**
     * Will try to guess the best database on current server.
     * Per example, if current server has MySQL and PostgreSQL it will pick up
     * PostgreSQL if MySQL doens't support InnoDB tables.
     *
     * If none of the above is
     *
     */
    public function suggestDatabaseType() {
        /**
         * @todo add database check for postgre
         */
        return $this->_suggestMysql() ? 'mysql' : (function_exists('pg_connect') ? 'pgsql' : (PHP5 ? 'sqlite' : 'mysql'));
    }

    public function _suggestMysql() {
        if(function_exists('mysql_connect')){
            if($db = @mysql_connect(   $this->getDatabaseHost(),
            $this->getDatabaseUser(),
            $this->getDatabasePassword())){
                return true;
            }
        }
        return false;
    }

    public function createDatabase($mode) {
        $success = true;

        $db = $this->databaseConnection('admin');

        if($db){
            if($this->getDatabaseType($mode) != 'sqlite'){
                $DataDict = NewDataDictionary($db);
                if($this->getDatabaseType($mode) == 'mysql'){
                    $success = $this->_createMysqlDatabase($db, $mode) ? $success : false;
                }
            }
            return $success;
        }
        return false;
    }

    public function _createMysqlDatabase(&$db, $mode) {
        $success = true;
        $success = $db->Execute('CREATE DATABASE '.$this->getDatabaseName($mode)) ? $success : false;
        $success = $db->Execute("GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER ON ".
        $this->getDatabaseName($mode).".* TO '".$this->getDatabaseUser($mode)."'@'".
        $this->getDatabaseHost($mode)."' IDENTIFIED BY '".$this->getDatabasePassword($mode)."'") ? $success : false;
        return $success;
    }

    public function getDatabaseHost($mode = '') {
        return !isset($this->{$mode.'_database_host'}) ? $this->suggestDatabaseHost() : $this->{$mode.'_database_host'};
    }

    public function getDatabaseUser($mode = '') {
        return !isset($this->{$mode.'_database_user'}) ? $this->suggestUserName() : $this->{$mode.'_database_user'};
    }

    public function getDatabasePassword($mode = '') {
        return !isset($this->{$mode.'_database_password'}) ? '' : $this->{$mode.'_database_password'};
    }

    public function getDatabaseType() {
        return !isset($this->database_type) ? $this->suggestDatabaseType() : $this->database_type;
    }

    public function setDatabaseType($database_type) {
        $database_type = strtolower($database_type);
        if(!in_array($database_type, array_keys($this->available_databases))){
            trigger_error(Ak::t('Selected database is not supported yet by the PhpOnRails Framework',array(),'framework_setup'));
        }elseif(!$this->isDatabaseDriverAvalible($database_type)){
            trigger_error(Ak::t('Could not set %database_type as database type. Your current PHP settings do not support %database_type databases', array('%database_type '=>$database_type), 'framework_setup'));
        }else{
            $this->database_type = $database_type;
            return $this->database_type;
        }
        return false;
    }

    public function getDatabaseName($mode) {
        return !isset($this->{$mode.'_database_name'}) ?
        $this->guessApplicationName().($mode=='development'?'_dev':($mode=='testing'?'_tests':'')) :
        $this->{$mode.'_database_name'};
    }

    public function setDatabaseName($database_name, $mode) {
        $this->{$mode.'_database_name'} = $database_name;
    }

    public function setDatabaseHost($host, $mode) {
        $this->{$mode.'_database_host'} = $host;
    }

    public function setDatabaseUser($user, $mode) {
        $this->{$mode.'_database_user'} = $user;
    }

    public function setDatabasePassword($password, $mode) {
        $this->{$mode.'_database_password'} = $password;
    }


    public function getDatabaseAdminUser() {
        return !isset($this->admin_database_user) ? 'root' : $this->admin_database_user;
    }

    public function getDatabaseAdminPassword() {
        return !isset($this->admin_database_password) ? '' : $this->admin_database_password;
    }


    public function setDatabaseAdminUser($user) {
        $this->admin_database_user = $user;
    }

    public function setDatabaseAdminPassword($password) {
        $this->admin_database_password = $password;
    }


    public function databaseConnection($mode) {
        static $connections = array();
        require_once(CONTRIB_DIR.DS.'adodb'.DS.'adodb.inc.php');

        $dsn = $this->_getDsn($mode);
        if(!isset($connections[$dsn])){
            if(!$connections[$dsn] = @NewADOConnection($dsn)){
                unset($connections[$dsn]);
                return false;
            }
        }
        return $connections[$dsn];
    }

    public function _getDsn($mode) {
        if($mode == 'admin'){
            $db_type = $this->getDatabaseType('production');
            return $db_type.'://'.
            $this->getDatabaseAdminUser().':'.
            $this->getDatabaseAdminPassword().'@'.$this->getDatabaseHost('production').($db_type == 'mysql' ? '/mysql' : '');
        }else{
            return $this->getDatabaseType() == 'sqlite' ?
            "sqlite://".urlencode(CONFIG_DIR.DS.$this->getDatabaseName($mode).'-'.$this->random.'.sqlite') :
            $this->getDatabaseType($mode)."://".$this->getDatabaseUser($mode).":".$this->getDatabasePassword($mode).
            "@".$this->getDatabaseHost($mode)."/".$this->getDatabaseName($mode);
        }
    }


    public function getAvailableDatabases() {
        $databases = array();
        foreach ($this->available_databases as $type=>$description){
            if($this->isDatabaseDriverAvalible($type)){
                $databases[] = array('type' => $type, 'name' => $description);
            }

        }
        return $databases;
    }


    public function isDatabaseDriverAvalible($database_type = null) {
        $database_type = empty($database_type) ? $this->getDatabaseType() : $database_type;
        if(strstr($database_type,'mysql')){
            $function = 'mysql_connect';
        }elseif (strstr($database_type,'pg') || strstr($database_type,'gre')){
            $function = 'pg_connect';
        }elseif (strstr($database_type,'lite')){
            $function = 'sqlite_open';
        }else{
            $function = $database_type.'_connect';
        }
        return function_exists($function);
    }

    public function getUrlSuffix() {
        return empty($this->url_suffix) ? SITE_URL_SUFFIX : $this->url_suffix;
    }


    public function getConfigurationFile($settings = array()) {
        $configuration_template = <<<CONFIG
<?php
// 项目配置及启动文�?//时区设置


// 当前项目环境设置: development, testing, production
// 会加载�?本environment目录下的�?环境}.php
defined('ENVIRONMENT') || define('ENVIRONMENT', 'development');
//设置项目可用的语�?�?逗号�?���?define('AVAILABLE_LOCALES', 'en,cn');
// Use this in order to allow only these locales on web requests
//~ define('ACTIVE_RECORD_DEFAULT_LOCALES',  AVAILABLE_LOCALES);
//~ define('APP_LOCALES',                    AVAILABLE_LOCALES);
//~ define('PUBLIC_LOCALES',                 AVAILABLE_LOCALES);
defined('URL_REWRITE_ENABLED') || define('URL_REWRITE_ENABLED', false);

defined('DS')                   || define('DS',                     DIRECTORY_SEPARATOR);
include dirname(__FILE__).DS.'environment.php';


CONFIG;


        if(empty($settings)){
            $settings = array();
            foreach (array('production','development','testing') as $mode){
                $settings['%'.$mode.'_database_type'] = $this->getDatabaseType($mode);
                if($settings['%'.$mode.'_database_type'] == 'sqlite'){

                    $settings['%'.$mode.'_database_file'] = CONFIG_DIR.DS.$this->getDatabaseName($mode).'-'.$this->random.'.sqlite';
                    $settings['%'.$mode.'_database_user'] =
                    $settings['%'.$mode.'_database_password'] =
                    $settings['%'.$mode.'_database_host'] =
                    $settings['%'.$mode.'_database_name'] = '';
                }else{
                    $settings['%'.$mode.'_database_user'] = $this->getDatabaseUser($mode);
                    $settings['%'.$mode.'_database_password'] = $this->getDatabasePassword($mode);
                    $settings['%'.$mode.'_database_host'] = $this->getDatabaseHost($mode);
                    $settings['%'.$mode.'_database_name'] = $this->getDatabaseName($mode);
                    $settings['%'.$mode.'_database_file'] = '';
                }
            }

            $settings['%ftp_settings'] = isset($this->ftp_enabled) ? 'ftp://'.$this->getFtpUser().':'.$this->getFtpPassword().'@'.$this->getFtpHost().$this->getFtpPath() : '';


            $settings['%locales'] = $this->getLocales();

            $settings['%URL_REWRITING'] = $this->isUrlRewriteEnabled() ? '' : "// The web configuration wizard could not detect if you have mod_rewrite enabled. \n// If that is the case, you should uncomment the next line line for better performance. \n// ";
            //~ $settings['%RAILS_DIR'] =  defined('RAILS_DIR') ?
            //~ "defined('RAILS_DIR') || define('RAILS_DIR', '".RAILS_DIR."');" : '';
             $settings['%RAILS_DIR'] = "defined('RAILS_DIR') || define('RAILS_DIR',".'dirname(__FILE).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."rails");';
        }

        return str_replace(array_keys($settings), array_values($settings), $configuration_template);

    }
    public function getDatabaseConfigurationFile($settings = array()) {

        $configuration_template = <<<CONFIG
production:
        adapter: %production_database_type
        host: %production_database_host
        database: %production_database_name
        username: %production_database_user
        password: %production_database_password
        port:
        database_file: %production_database_file
        options:


development:
        adapter: %development_database_type
        host: %development_database_host
        database: %development_database_name
        username: %development_database_user
        password: %development_database_password
        port:
        database_file: %development_database_file
        options:

# Warning: The database defined as 'testing' will be erased and
# re-generated from your development database when you run './script/test app'.
# Do not set this db to the same as development or production.
testing:
        adapter: %testing_database_type
        host: %testing_database_host
        database: %testing_database_name
        username: %testing_database_user
        password: %testing_database_password
        port:
        database_file: %testing_database_file
        options:

CONFIG;
        if(empty($settings)){
            $settings = array();
            foreach (array('production','development','testing') as $mode){
                $settings['%'.$mode.'_database_type'] = $this->getDatabaseType($mode);
                if($settings['%'.$mode.'_database_type'] == 'sqlite'){

                    $settings['%'.$mode.'_database_file'] = CONFIG_DIR.DS.$this->getDatabaseName($mode).'-'.$this->random.'.sqlite';
                    $settings['%'.$mode.'_database_user'] =
                    $settings['%'.$mode.'_database_password'] =
                    $settings['%'.$mode.'_database_host'] =
                    $settings['%'.$mode.'_database_name'] = '';
                }else{
                    $settings['%'.$mode.'_database_user'] = $this->getDatabaseUser($mode);
                    $settings['%'.$mode.'_database_password'] = $this->getDatabasePassword($mode);
                    $settings['%'.$mode.'_database_host'] = $this->getDatabaseHost($mode);
                    $settings['%'.$mode.'_database_name'] = $this->getDatabaseName($mode);
                    $settings['%'.$mode.'_database_file'] = '';
                }
            }

        }

        return str_replace(array_keys($settings), array_values($settings), $configuration_template);

    }
    public function writeConfigurationFile($configuration_details) {
        if($this->canWriteConfigurationFile()){
            return AkFileSystem::file_put_contents(CONFIG_DIR.DS.'config.php', $configuration_details);
        }
        return false;
    }
    public function writeDatabaseConfigurationFile($configuration_details) {
        if($this->canWriteDbConfigurationFile()){
            return AkFileSystem::file_put_contents(CONFIG_DIR.DS.'database.yml', $configuration_details);
        }
        return false;
    }
    public function canWriteConfigurationFile() {
        if(isset($this->ftp_enabled)){
            $this->testFtpSettings();
        }
        $file_path = CONFIG_DIR.DS.'config.php';
	//~ echo "{$file_path}".!file_exists($file_path);
        return !file_exists($file_path);
    }
    public function canWriteDbConfigurationFile() {
        if(isset($this->ftp_enabled)){
            $this->testFtpSettings();
        }
        $file_path = CONFIG_DIR.DS.'database.yml';
        return !file_exists($file_path);
    }

    public function modifyHtaccessFiles() {
        if($this->isUrlRewriteEnabled()){
            return true;
        }

        if(isset($this->ftp_enabled)){
            $this->testFtpSettings();
        }
        $file_1 = BASE_DIR.DS.'.htaccess';
        $file_2 = PUBLIC_DIR.DS.'.htaccess';
        $file_1_content = file_exists($file_1) ? AkFileSystem::file_get_contents($file_1) : '';
        $file_2_content = file_exists($file_2) ? AkFileSystem::file_get_contents($file_2) : '';

        $url_suffix = $this->getUrlSuffix();

        $url_suffix = $url_suffix[0] != '/' ? '/'.$url_suffix : $url_suffix;

        empty($file_1_content) || @AkFileSystem::file_put_contents($file_1, str_replace('# RewriteBase /framework',' RewriteBase '.$url_suffix, $file_1_content));
        empty($file_2_content) || @AkFileSystem::file_put_contents($file_2, str_replace('# RewriteBase /framework',' RewriteBase '.$url_suffix, $file_2_content));
    }

    public function isUrlRewriteEnabled() {
        return @file_get_contents(SITE_URL.'/framework_setup/url_rewrite_check') == 'url_rewrite_working';
    }

    public function getApplicationName() {
        if(!isset($this->application_name)){
            $this->setApplicationName($this->guessApplicationName());
        }
        return $this->application_name;
    }

    public function setApplicationName($application_name) {
        $this->application_name = $application_name;
    }

    public function guessApplicationName() {
        $application_name = Ak::last(explode('/',SITE_URL_SUFFIX));
        $application_name = empty($application_name) ? substr(BASE_DIR, strrpos(BASE_DIR, DS)+1) : $application_name;
        return empty($application_name) ? 'my_app' : $application_name;
    }

    public function canWriteToTempDir() {
        return $this->_writeToTemporaryFile(BASE_DIR.DS.'tmp'.DS.'test_file.txt');
    }

    public function canWriteToLocaleDir() {
        return $this->_writeToTemporaryFile(APP_DIR.DS.'locales'.DS.'test_file.txt');
    }

    public function canWriteToPublicDir() {
        return $this->_writeToTemporaryFile(PUBLIC_DIR.DS.'test_file.txt');
    }

    public function needsFtpFileHandling() {
        return !$this->_writeToTemporaryFile(CONFIG_DIR.DS.'test_file.txt');
    }

    public function _writeToTemporaryFile($file_path, $content = '', $mode = 'a+') {
        $result = false;
        if(strstr($file_path, BASE_DIR)){
            if(!$fp = @fopen($file_path, $mode)) {
                return false;
            }
            $this->_temporaryFilesCleanUp($file_path);
            $result = @fwrite($fp, $content);
            if (false !== $result){
                $result = true;
            }
            @fclose($fp);
        }
        return $result;
    }

    public function _temporaryFilesCleanUp($file_path = null) {
        static $file_paths = array();
        if($file_path == null && count($file_paths) > 0){
            foreach ($file_paths as $file_path){
                // we try to prevent removing nothing outside the framework
                if(strstr($file_path, BASE_DIR)){
                    @unlink($file_path);
                }
            }
            return ;
        }elseif (!empty($file_path) &&  count($file_paths) == 0){
            register_shutdown_function(array($this, '_temporaryFilesCleanUp'));
        }
        $file_paths[$file_path] = $file_path;
    }

    public function addSetupOptions($options = array()) {
        $options = array_merge($this->getDefaultOptions(), $options);

        if(!$this->isDatabaseDriverAvalible($options['database']['type'])){
            $this->addError(Ak::t('Your current PHP settings do not have support for %database_type databases.',
            array('%database_type'=>$options['database']['type']),'framework_setup'));
        }elseif(!$db = $this->databaseConnection(
        $options['database']['type'],
        $options['database']['host'],
        $options['database']['user'],
        $options['database']['password'],
        $options['database']['name'])){
            $this->addError(Ak::t('Could not connect to the database using %details', array(), 'framework_setup'),
            array('%details'=>var_dump($options['database'])));
        }

        $options['server']['locales'] = str_replace(' ','', $options['server']['locales']);
        $options['server']['locales']  = empty($options['server']['locales']) ? 'cn' : $options['server']['locales'];

        foreach ($options as $group=>$details){
            if(!is_array($details)){
                continue;
            }
            foreach ($details as $detail=>$value){
                $this->{$group.'_'.$detail} = $value;
            }
        }

        $this->options = $options;
    }

    public function getSetupOptions() {
        return isset($this->options) ? $this->options : array();
    }

    public function addError($error) {
        $this->errors[] = $error;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getDefaultOptions() {
        return array(
        'production_database_type'      => $this->getDatabaseType('production'),
        'production_database_host'      => $this->getDatabaseHost('production'),
        'production_database_name'      => $this->getDatabaseName('production'),
        'production_database_user'      => $this->getDatabaseUser('production'),
        'production_database_password'  => '',

        'development_database_type'     => $this->getDatabaseType('development'),
        'development_database_host'     => $this->getDatabaseHost('development'),
        'development_database_name'     => $this->getDatabaseName('development'),
        'development_database_user'     => $this->getDatabaseUser('development'),
        'development_database_password' => '',

        'testing_database_type'         => $this->getDatabaseType('testing'),
        'testing_database_host'         => $this->getDatabaseHost('testing'),
        'testing_database_name'         => $this->getDatabaseName('testing'),
        'testing_database_user'         => $this->getDatabaseUser('testing'),
        'testing_database_password'     => '',

        'admin_database_user'           => $this->getDatabaseAdminUser(),
        'admin_database_password'       => $this->getDatabaseAdminPassword(),

        'url_suffix'                    => trim(SITE_URL_SUFFIX, '/'),
        'locales'                       => join(',',$this->suggestLocales()),
        'ftp_user'                      => $this->getFtpUser(),
        'ftp_host'                      => $this->getFtpHost(),
        'ftp_path'                      => $this->getFtpPath(),

        'random'                        => Ak::randomString(),
        );
    }

    public function canUseFtpFileHandling() {
        return function_exists('ftp_connect');
    }

    public function getFtpHost() {
        if(!isset($this->ftp_host)){
            $details = explode('/', str_replace(array('http://','https://','www.'),array('','','ftp.'), SITE_URL).'/');
            return array_shift($details);
        }
        return $this->ftp_host;
    }

    public function setFtpHost($ftp_host) {
        $this->ftp_host = trim($ftp_host, '/');
    }

    public function getFtpPath() {
        if(!isset($this->ftp_path)){
            return '/'.trim(join('/',array_slice(
            explode('/',
            str_replace(array('http://','https://'),'', SITE_URL).'/'),1)
            ),'/');
        }
        return $this->ftp_path;
    }

    public function setFtpPath($ftp_path) {
        $this->ftp_path = empty($ftp_path) ? '' : '/'.trim($ftp_path,'/');
    }

    public function getFtpUser() {
        return !isset($this->ftp_user) ? $this->suggestUserName() : $this->ftp_user;
    }

    public function setFtpUser($ftp_user) {
        $this->ftp_user = $ftp_user;
    }

    public function getFtpPassword() {
        return !isset($this->ftp_password) ? '' : $this->ftp_password;
    }

    public function setFtpPassword($ftp_password) {
        $this->ftp_password = $ftp_password;
    }

    public function setDefaultOptions() {
        foreach ($this->getDefaultOptions() as $k=>$v){
            $this->$k = $v;
        }
    }

    public function hasUrlSuffix() {
        return !empty($this->url_suffix) && trim($this->url_suffix,'/') != '';
    }

    public function suggestUserName() {
        if(WIN){
            return 'root';
        }
        $script_owner = get_current_user();
        return  $script_owner == '' ? 'root' : $script_owner;
    }

    public function testFtpSettings() {
        if(!$this->canUseFtpFileHandling()){
            return false;
        }

        $ftp_path = 'ftp://'.$this->getFtpUser().':'.$this->getFtpPassword().'@'.
        $this->getFtpHost().$this->getFtpPath();

        defined('UPLOAD_FILES_USING_FTP') || define('UPLOAD_FILES_USING_FTP',    true);
        defined('READ_FILES_USING_FTP')   || define('READ_FILES_USING_FTP',      false);
        defined('DELETE_FILES_USING_FTP') || define('DELETE_FILES_USING_FTP',    true);
        defined('FTP_PATH')               || define('FTP_PATH',                  $ftp_path);
        defined('FTP_AUTO_DISCONNECT')    || define('FTP_AUTO_DISCONNECT',       true);

        if(AkFileSystem::file_put_contents(CONFIG_DIR.DS.'test_file.txt','hello from ftp')){
            $text = AkFileSystem::file_get_contents(CONFIG_DIR.DS.'test_file.txt');
            AkFileSystem::file_delete(CONFIG_DIR.DS.'test_file.txt');
        }

        $this->ftp_enabled = (isset($text) && $text == 'hello from ftp');

        return $this->ftp_enabled;
    }

    public function getLocales() {
        return join(',',!isset($this->locales) ? $this->suggestLocales() : $this->_getLocales($this->locales));
    }

    public function setLocales($locales) {
        $this->locales = $this->_getLocales($locales);
    }

    public function _getLocales($locales) {
        return array_map('trim',array_unique(array_diff((is_array($locales) ? $locales : explode(',',$locales.',')), array(''))));
    }

    public function suggestLocales() {
        $LocaleManager = new AkLocaleManager();

        $langs = array('cn');
        if(WIN){
            $langs[] = @$_ENV['LANG'];
        }
        $langs = array_merge($langs, $LocaleManager->getBrowserLanguages());

        return array_unique(array_map('strtolower',array_diff($langs,array(''))));
    }

    public function suggestDatabaseHost() {
        return 'localhost';
    }

    public function relativizeStylesheetPaths() {
        $asset_path = $this->_getAssetBasePath();
        if($this->hasUrlSuffix() || !empty($asset_path)){
            $url_suffix = trim($this->getUrlSuffix(),'/');
            if(!empty($asset_path)){
                $url_suffix = trim($url_suffix.'/'.$asset_path,'/');
            }
            foreach ($this->stylesheets as $stylesheet) {
                $filename = PUBLIC_DIR.DS.'stylesheets'.DS.$stylesheet.'.css';
                $relativized_css = preg_replace("/url\((\'|\")?\/images/","url($1/$url_suffix/images", @AkFileSystem::file_get_contents($filename));
                empty($relativized_css) || @AkFileSystem::file_put_contents($filename, $relativized_css);
            }
        }
    }

    public function _getAssetBasePath() {
        return defined('INSECURE_APP_DIRECTORY_LAYOUT') && INSECURE_APP_DIRECTORY_LAYOUT ? 'public' : '';
    }
}


set_time_limit(0);
error_reporting(-1);

require_once(CONTRIB_DIR.DS.'adodb'.DS.'adodb.inc.php');
require_once (CONTRIB_DIR.DS.'pear'.DS.'Console'.DS.'Getargs.php');


function prompt_var($question, $default_value = null, $cli_value = null)
{
    global $options;
    if(empty($options['interactive']) && isset($cli_value)){
	echo 'prompt_var:'.$cli_value."\n";
        return $cli_value;
    }else{
        return AkConsole::promptUserVar($question, array('default'=>$default_value, 'optional'=>true));
    }
}

function set_db_user_and_pass(&$FrameworkSetup, &$db_user, &$db_pass, &$db_type, $defaults = true){
    global $options;
    $db_type = prompt_var('Database type', 'mysql', $defaults?@$options['database']:null);
    $db_user = prompt_var('Database user', $FrameworkSetup->suggestUserName(), $defaults?@$options['user']:null);
    $db_pass = prompt_var('Database password', '', $defaults?@$options['password']:null);


    if(!@NewADOConnection("$db_type://$db_user:$db_pass@localhost")){
        echo Ak::t('Could not connect to the database'."\n");
        set_db_user_and_pass($FrameworkSetup, $db_user, $db_pass, $db_type, false);
    }

}





$config =  array(

'user' => array(
'short'   => 'u',
'max'     => 1,
'min'     => 1,
'default' => 'root',
'desc'    => 'Database user'),

'password' => array(
'short'   => 'p',
'max'     => 1,
'min'     => 1,
'default' => '',
'desc'    => 'Database password'),

'database' => array(
'short'   => 'd',
'max'     => 1,
'min'     => 1,
'default' => 'mysql',
'desc'    => 'Database type'),

'name' => array(
'short'   => 'n',
'max'     => 1,
'min'     => 1,
'default'     => AkInflector::underscore(basename(BASE_DIR)),
'desc'    => 'Database name. This is the name of your database. It will be prefixed with _dev and _tests for non production environments.'),

'languages' => array(
'short'   => 'l',
'max'     => 1,
'min'     => 1,
'default' => 'cn',
'desc'    => 'Language codes for this application.'),

'interactive' => array(
'short'   => 'i',
'max'     => 0,
'min'     => 0,
'default' => false,
'desc'    => 'Interactive mode.'),

);


$args = Console_Getargs::factory($config);

if (PEAR::isError($args)) {
    if ($args->getCode() === CONSOLE_GETARGS_ERROR_USER) {
        echo Console_Getargs::getHelp($config, null, $args->getMessage())."\n";
    } else if ($args->getCode() === CONSOLE_GETARGS_HELP) {
        echo @Console_Getargs::getHelp($config)."\n";
    }
    exit;
}

$options = $args->getValues();
//~ echo(serialize($options));



$FrameworkSetup = new FrameworkSetup();
$FrameworkSetup->setDefaultOptions();
$FrameworkSetup->getAvailableDatabases();

$app_name = empty($options['interactive']) ? 
		$options['name'] :
			prompt_var('Database name. This is the name of your database. 
			It will be prefixed with _dev and _tests for non production environments.',
			$options['name']);

$db_user = $db_pass = $db_type = '';

echo 'set_db_user_and_pass';


set_db_user_and_pass($FrameworkSetup, $db_user, $db_pass, $db_type);


$FrameworkSetup->setDatabaseUser($db_user, 'admin');
$FrameworkSetup->setDatabasePassword($db_pass, 'admin');
$FrameworkSetup->setDatabaseType($db_type);

foreach (array('development','production','testing') as $mode){
    $db_postfix = ($mode=='production'?'':
    ($mode=='development'?'_dev':
    ($mode=='testing'?'_tests':'_'.$mode)));

    $FrameworkSetup->setDatabaseName(
    prompt_var(ucfirst($mode).' database name',
    $app_name.$db_postfix, @$options['name'].$db_postfix), $mode);

    if($FrameworkSetup->getDatabaseType($mode) != 'sqlite'){
        $FrameworkSetup->setDatabaseHost('localhost', $mode);
        $FrameworkSetup->setDatabaseUser($db_user, $mode);
        $FrameworkSetup->setDatabasePassword($db_pass, $mode);
        $FrameworkSetup->createDatabase($mode);
    }
}

$FrameworkSetup->setLocales(prompt_var('Application Locales', 'cn', @$options['languages']));

$configuration_file = $FrameworkSetup->getConfigurationFile();
//~ echo 'configuration file name:'.$configuration_file;
$db_configuration_file = $FrameworkSetup->getDatabaseConfigurationFile();


$FrameworkSetup->writeConfigurationFile($configuration_file);
$FrameworkSetup->writeDatabaseConfigurationFile($db_configuration_file);
echo "\nYour application has been confirured correctly\n";
echo "\nSee config/config.php and config/database.yml\n";

