<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details


/**
 * Plugin manager
 *
 * @package Plugins
 * @subpackage Manager
 * @author Bermi Ferrer <bermi a.t bermilabs c.om> 2007
 */


/**
 * Plugin manager
 *
 * @package Plugins
 * @subpackage Manager
 * @author Bermi Ferrer <bermi a.t bermilabs c.om> 2007
  */
class AkPluginManager
{

    /**
     * Main repository, must be an Apache mod_svn interface to subversion. Defaults to PLUGINS_MAIN_REPOSITORY.
     * @var    string
     * @access public
     */
    public $main_repository = PLUGINS_MAIN_REPOSITORY;

    /**
     * Repository discovery page.
     *
     * A wiki page containing links to repositories. Links on that wiki page
     * must link to an http:// protocol (no SSL yet) and end in plugins.
     * Defaults to  PLUGINS_REPOSITORY_DISCOVERY_PAGE
     * @var    string
     * @access public
     */
    public $respository_discovery_page = PLUGINS_REPOSITORY_DISCOVERY_PAGE;


    public function __construct() {
        @set_time_limit(0);
        @ini_set('memory_limit', -1);
    }

    /**
     * Gets a list of available repositories.
     *
     * @param  boolean $force_reload Forces reloading, useful for testing and when running as an application server.
     * @return array   List of repository URLs
     * @access public
     */
    public function getAvailableRepositories($force_reload = false) {
        if(!empty($this->tmp_repositories)){
            return $this->tmp_repositories;
        }

        if($force_reload || empty($this->repositories)){
            $this->repositories = array($this->main_repository);
            if(file_exists($this->_getRepositoriesConfigPath())){
                $repository_candidates = array_diff(array_map('trim', explode("\n",AkFileSystem::file_get_contents($this->_getRepositoriesConfigPath()))), array(''));
                if(!empty($repository_candidates)){
                    foreach ($repository_candidates as $repository_candidate){
                        if(strlen($repository_candidate) > 0 && $repository_candidate[0] != '#' && strstr($repository_candidate,'plugins')){
                            $this->repositories[] = $repository_candidate;
                        }
                    }
                }
            }
        }
        return $this->repositories;
    }



    /**
     * Ads a repository to the know repositories list.
     *
     * @param  string $repository_path  An Apache mod_svn interface to subversion.
     * @return void
     * @access public
     */
    public function addRepository($repository_path) {
        if(!in_array(trim($repository_path), $this->getAvailableRepositories(true))){
            AkFileSystem::file_add_contents($this->_getRepositoriesConfigPath(), $repository_path."\n");
        }
    }



    /**
     * Removes a repository to the know repositories list.
     *
     * @param  string $repository_path  An Apache mod_svn interface to subversion.
     * @return boolean Returns false if the repository was not available
     * @access public
     */
    public function removeRepository($repository_path) {
        if(file_exists($this->_getRepositoriesConfigPath())){
            $repositories = AkFileSystem::file_get_contents($this->_getRepositoriesConfigPath());
            if(!strstr($repositories, $repository_path)){
                return false;
            }
            $repositories = str_replace(array($repository_path, "\r", "\n\n"), array('', "\n", "\n"), $repositories);
            AkFileSystem::file_put_contents($this->_getRepositoriesConfigPath(), $repositories);
        }
    }



    /**
     * Gets a list of available plugins.
     *
     * Goes through each trusted plugin server and retrieves the name of the
     * folders (plugins) on the repository path.
     *
     * @param  boolean $force_update If it is not set to true, it will only check remote sources once per hour
     * @return array   Returns an array containing "plugin_name" => "repository URL"
     * @access public
     */
    public function getPlugins($force_update = false) {
        if($force_update || !is_file($this->_getRepositoriesCahePath()) || filemtime($this->_getRepositoriesCahePath()) > 3600){
            if(!$this->_updateRemotePluginsList()){
                return array();
            }
        }

        return array_map('trim', Ak::convert('yaml', 'array', AkFileSystem::file_get_contents($this->_getRepositoriesCahePath())));
    }



    /**
     * Retrieves a list of installed plugins
     *
     * @return array  Returns an array with the plugins available at PLUGINS_DIR
     * @access public
     */
    public function getInstalledPlugins() {
        $Loader = new AkPluginLoader();
        return $Loader->getAvailablePlugins();
    }



    /**
     * Installs a plugin
     *
     * Install a plugin from a remote resource.
     *
     * Plugins can have an Rails installer at located at "plugin_name/installer/plugin_name_installer.php"
     * If the installer is available, it will run the "PluginNameInstaller::install()" method, which will trigger
     * all the up_* methods for the installer.
     *
     * @param  string  $plugin_name Plugin name
     * @param  unknown $repository   An Apache mod_svn interface to subversion. If not provided it will use a trusted repository.
     * @param  array $options
     * - externals: Use svn:externals to grab the plugin. Enables plugin updates and plugin versioning.
     * - checkout:  Use svn checkout to grab the plugin. Enables updating but does not add a svn:externals entry.
     * - revision:  Checks out the given revision from subversion. Ignored if subversion is not used.
     * - force:     Overwrite existing files.
     * @return mixed Returns false if the plugin can't be found.
     * @access public
     */
    public function installPlugin($plugin_name, $repository = null, $options = array()) {
        $default_options = array(
        'externals' => false,
        'checkout' => false,
        'force' => false,
        'revision' => null,
        );

        $options = array_merge($default_options, $options);

        $install_method = $this->guessBestInstallMethod($options);

        if($install_method != 'git'){
            $plugin_name = Ak::sanitize_include($plugin_name, 'high');
            if($install_method != 'local directory'){
                $repository = $this->getRepositoryForPlugin($plugin_name, $repository);
            }
        }

        if(!$options['force'] && is_dir(PLUGINS_DIR.DS.$plugin_name)){
            AkConsole::displayError(Ak::t('Destination directory is not empty. Use force option to overwrite exiting files.'), true);
        }else{
            $method = '_installUsing'.AkInflector::camelize($install_method);
            $this->$method($plugin_name, rtrim($repository, '/'), $options['revision'], $options['force']);
            $this->_runInstaller($plugin_name, 'install', $options);
        }
    }

    public function guessBestInstallMethod($options = array()) {
        if(defined('BEST_PLUGIN_INSTALL_METHOD') && in_array(BEST_PLUGIN_INSTALL_METHOD,
        array('local directory', 'checkout', 'export', 'http'))){
            return BEST_PLUGIN_INSTALL_METHOD;
        }
	//~ var_dump($options['parameters']);
        if(!empty($options['parameters']) && is_dir($options['parameters'])){
            return 'local directory';
        }elseif($this->canUseGit()){
                return 'git';
        }elseif($this->canUseSvn()){
            if(!empty($options['externals']) && $this->_shouldUseSvnExternals()){
                return 'externals';
            }elseif(!empty($options['checkout']) && $this->_shouldUseSvnCheckout()){
                return 'checkout';
            }
            return 'export';
        }else{
            return 'http';
        }
    }

    public function canUseGit() {
        return strstr(`git --version`, 'git version');
    }

    public function canUseSvn() {
        return strstr(`svn --version`, 'CollabNet');
    }


    /**
     * Updates a plugin if there are changes.
     *
     * Uses subversion update if available. If http update is used, it will
     * download the whole plugin unless there is a CHANGELOG file, in which case
     * it will only perform the update if there are changes.
     *
     * @param  string  $plugin_name Plugin name
     * @param  string $repository   An Apache mod_svn interface to subversion. If not provided it will use a trusted repository.
     * @return null
     * @access public
     */
    public function updatePlugin($plugin_name, $repository = null) {
        $options = array(
        'externals' => false,
        'checkout' => false
        );

        $plugin_name = Ak::sanitize_include($plugin_name, 'high');

        $method = '_updateUsing'.AkInflector::camelize($this->guessBestInstallMethod($options));
        $this->$method($plugin_name, rtrim($this->getRepositoryForPlugin($plugin_name, $repository), '/'));

        $this->_runInstaller($plugin_name, 'install');
    }


    /**
     * Uninstalls an existing plugin
     *
     * Plugins can have an Rails installer at located at "plugin_name/installer/plugin_name_installer.php"
     * If the installer is available, it will run the "PluginNameInstaller::uninstall()" method, which will trigger
     * all the down_* methods for the installer.
     *
     * @param  string  $plugin_name Plugin name
     * @return void
     * @access public
     */
    public function uninstallPlugin($plugin_name) {
        $plugin_name = Ak::sanitize_include($plugin_name, 'high');
        $this->_runInstaller($plugin_name, 'uninstall');
        if(is_dir(PLUGINS_DIR.DS.$plugin_name)){
            AkFileSystem::directory_delete(PLUGINS_DIR.DS.$plugin_name);
        }
        if($this->_shouldUseSvnExternals()){
            $this->_uninstallExternals($plugin_name);
        }
    }


    /**
     * Gets a list of repositories available at the web page defined by PLUGINS_REPOSITORY_DISCOVERY_PAGE (http://www.rails.org/wiki/plugins by default)
     *
     * @return array An array of non trusted repositories available at http://www.rails.org/wiki/plugins
     * @access public
     */
    public function getDiscoveredRepositories() {
        return array_diff($this->getRepositoriesFromRemotePage(), $this->getAvailableRepositories(true));
    }


    /**
     * Returns the repository for a given $plugin_name
     *
     * @param  string  $plugin_name     The name of the plugin
     * @param  string  $repository  If a repository name is provided it will check for the plugin name existance.
     * @return mixed Repository URL or false if plugin can't be found
     * @access public
     */
    public function getRepositoryForPlugin($plugin_name, $repository = null) {
        if(empty($repository)){
            $available_plugins = $this->getPlugins();
        }elseif($this->_isGitRepo($plugin_name)){
            return $repository;
        }else{
            $available_plugins = array();
            $this->_addAvailablePlugins_($repository, $available_plugins);
        }

        if(empty($available_plugins[$plugin_name])){
            AkConsole::displayError(Ak::t('Could not find %plugin_name plugin', array('%plugin_name' => $plugin_name)), true);
        }elseif (empty($repository)){
            $repository = $available_plugins[$plugin_name];
        }
        return $repository;
    }

    /**
     * Retrieves the URL's from the PLUGINS_REPOSITORY_DISCOVERY_PAGE (http://www.rails.org/wiki/plugins by default)
     *
     * Plugins in that page must follow this convention:
     *
     *  * Only http:// protocol. No https:// or svn:// support yet
     *  * The URL must en in plugins to be fetched automatically
     *
     * @return array   An array of existing repository URLs
     * @access public
     */
    public function getRepositoriesFromRemotePage() {

        $repositories = array();
        if(preg_match_all('/href="(http:\/\/(?!www\.rails\.org\/wiki\/)[^"]*plugins)/', Ak::url_get_contents($this->respository_discovery_page), $matches)){
            $repositories = array_unique($matches[1]);
        }
        return $repositories;
    }

    /**
     * Runs the plugin installer/uninstaller if available
     *
     * Plugins can have an Rails installer at located at "plugin_name/installer/plugin_name_installer.php"
     * If the installer is available, it will run the "PluginNameInstaller::install/uninstall()" method, which will trigger
     * all the up/down_* methods for the installer.
     *
     * @param  string  $plugin_name     The name of the plugin
     * @param  string  $install_or_uninstall What to do, options are install or uninstall
     * @return void
     * @access private
     */
    private function _runInstaller($plugin_name, $install_or_uninstall = 'install', $options = array()) {
        $plugin_name = $this->_getPluginDir($plugin_name);
        $plugin_dir = AkConfig::getDir('plugins').DS.$plugin_name;
        //~ var_dump($plugin_dir, $plugin_name);
        if(file_exists($plugin_dir.DS.'installer'.DS.$plugin_name.'_installer.php')){
            require_once($plugin_dir.DS.'installer'.DS.$plugin_name.'_installer.php');
            $class_name = AkInflector::camelize($plugin_name.'_installer');
            if(class_exists($class_name)){
                $Installer = new $class_name(null,$plugin_name);
                $Installer->options = $options;
                $Installer->db->debug = false;
                $Installer->warn_if_same_version = false;
                $Installer->$install_or_uninstall();
            }
        }
    }

    /**
     * Copy recursively a remote svn dir into a local path.
     *
     * Downloads recursively the contents of remote directories from a mod_svn Apache subversion interface to a local destination.
     *
     * File or directory permissions are not copied, so you will need to use installers to fix it if required.
     *
     * @param  string  $source      An Apache mod_svn interface to subversion URL.
     * @param  string  $destination Destination directory
     * @return void
     * @access private
     */
    private function _copyRemoteDir($source, $destination) {
        $dir_name = trim(substr($source, strrpos(rtrim($source, '/'), '/')),'/');
        AkFileSystem::make_dir($destination.DS.$dir_name);

        list($directories, $files) = $this->_parseRemoteAndGetDirectoriesAndFiles($source);

        foreach ($files as $file){
            $this->_copyRemoteFile($source.$file, $destination.DS.$dir_name.DS.$file);
        }

        foreach ($directories as $directory){
            $this->_copyRemoteDir($source.$directory.'/', $destination.DS.$dir_name);
        }
    }



    /**
     * Copies a remote file into a local destination
     *
     * @param  string $source      Source URL
     * @param  string  $destination Destination directory
     * @return void
     * @access private
     */
    private function _copyRemoteFile($source, $destination) {
        AkFileSystem::file_put_contents($destination, Ak::url_get_contents($source));
    }



    /**
     * Performs an update of available cached plugins.
     *
     * @return boolean
     * @access private
     */
    private function _updateRemotePluginsList() {
        $new_plugins = array();
        foreach ($this->getAvailableRepositories() as $repository){
            $this->_addAvailablePlugins_($repository, $new_plugins);
        }
        if(empty($new_plugins)){
            AkConsole::displayError(Ak::t('Could not fetch remote plugins from one of these repositories: %repositories', array('%repositories' => "\n".join("\n", $this->getAvailableRepositories()))), true);
        }
        return AkFileSystem::file_put_contents($this->_getRepositoriesCahePath(), Ak::convert('array', 'yaml', $new_plugins));
    }



    /**
     * Modifies $plugins_list adding the plugins available at $repository
     *
     * @param  string $repository    Repository URL
     * @param  array   $plugins_list Plugins list in the format 'plugin_name' => 'repository'
     * @return void
     * @access private
     */
    private function _addAvailablePlugins_($repository, &$plugins_list) {
        list($directories) = $this->_parseRemoteAndGetDirectoriesAndFiles($repository);
        foreach ($directories as $plugin){
            if(empty($plugins_list[$plugin])){
                $plugins_list[$plugin] = $repository;
            }
        }
    }



    /**
     * Parses a remote Apache svn web page and returns a list of available files and directories
     *
     * @param  string $remote_path Repository URL
     * @return array   an array like array($directories, $files). Use list($directories, $files) = $this->_parseRemoteAndGetDirectoriesAndFiles($remote_path) for getting the results of this method
     * @access private
     */
    private function _parseRemoteAndGetDirectoriesAndFiles($remote_path) {
        $directories = $files = array();
        $remote_contents = Ak::url_get_contents(rtrim($remote_path, '/').'/');

        if(preg_match_all('/href="([A-Za-z\-_0-9]+)\/"/', $remote_contents, $matches)){
            foreach ($matches[1] as $directory){
                $directories[] = trim($directory);
            }
        }
        if(preg_match_all('/href="(\.?[A-Za-z\-_0-9\.]+)"/', $remote_contents, $matches)){
            foreach ($matches[1] as $file){
                $files[] = trim($file);
            }
        }
        return array($directories, $files);
    }



    /**
     * Trusted repositories location
     *
     * By default trusted repositories are located at config/plugin_repositories.txt
     *
     * @return string  Trusted repositories  path
     * @access private
     */
    private function _getRepositoriesConfigPath() {
        if(empty($this->tmp_repositories)){
            return AkConfig::getDir('config').DS.'plugin_repositories.txt';
        }else{
            return TMP_DIR.DS.'plugin_repositories.'.md5(serialize($this->tmp_repositories));
        }
    }



    /**
     * Cached informations about available plugins
     *
     * @return string  Plugin information cache path. By default TMP_DIR.DS.'plugin_repositories.yaml'
     * @access private
     */
    private function _getRepositoriesCahePath() {
        return TMP_DIR.DS.'plugin_repositories.yaml';
    }

    private function _shouldUseSvnExternals() {
        return is_dir(PLUGINS_DIR.DS.'.svn');
    }

    private function _shouldUseSvnCheckout() {
        return is_dir(PLUGINS_DIR.DS.'.svn');
    }

    private function _installUsingCheckout($name, $uri, $rev = null, $force = false) {
        $rev = empty($rev) ? '' : " -r $rev ";
        $force = $force ? ' --force ' : '';
        $plugin_dir = PLUGINS_DIR.DS.$name;
        `svn co $force $rev $uri/$name $plugin_dir`;
    }

    private function _updateUsingCheckout($name) {
        $plugin_dir = PLUGINS_DIR.DS.$name;
        `svn update $plugin_dir`;
    }

    private function _getPluginDir($plugin_name)
    {
        if($this->_isGitRepo($plugin_name)){
            return substr($plugin_name, 0, -4);
        }
        return $plugin_name;
    }

    private function _isGitRepo($name)
    {
        return substr($name, -4) == '.git';
    }

    private function _shouldUseGitSubmodule() {
        return is_dir(BASE_DIR.DS.'.git');
    }

    private function _installUsingGit($name, $uri, $rev = null, $force = false) {
        $rev = empty($rev) ? '' : " -r $rev ";
        $force = $force ? ' --force ' : '';
        $plugin_dir = PLUGINS_DIR.DS.$this->_getPluginDir($name);
        if($this->_shouldUseGitSubmodule()){
            `git submodule add $rev $uri/$name $plugin_dir`;
        }else{
            `git clone $force $rev $uri/$name $plugin_dir`;
        }
    }

    private function _installUsingLocalDirectory($name, $path, $rev = null) {
        $source = $path.DS.$name;
        $plugin_dir = PLUGINS_DIR;
        $command = !WIN ? 'cp -rf ' : 'xcopy /h /r /k /x /y /S /E ';
        `$command $source $plugin_dir`;
    }

    private function _updateUsingLocalDirectory($name) {
        AkConsole::displayError(Ak::t('Updating from local targets it\'s not supported yet. Please use install --force instead.'), true);
    }

    private function _installUsingExport($name, $uri, $rev = null, $force = false) {
        $rev = empty($rev) ? '' : " -r $rev ";
        $force = $force ? ' --force ' : '';
        $plugin_dir = PLUGINS_DIR.DS.$name;
        `svn export $force $rev $uri/$name $plugin_dir`;
    }

    private function _updateUsingExport($name, $uri) {
        $plugin_dir = PLUGINS_DIR.DS.$name;
        `svn export --force $uri/$name $plugin_dir`;
    }

    private function _installUsingExternals($name, $uri, $rev = null, $force = false) {
        $extras = empty($rev) ? '' : " -r $rev ";
        $extras .= ($force ? ' --force ' : '');
        $externals = $this->_getExternals();
        $externals[$name] = $uri;
        $this->_setExternals($externals, $extras);
        $this->_installUsingCheckout($name, $uri, $rev, $force);
    }

    private function _updateUsingExternals($name) {
        $this->_updateUsingCheckout($name);
    }

    private function _updateUsingHttp($name, $uri) {
        if(is_file(PLUGINS_DIR.DS.$name.DS.'CHANGELOG') &&
        md5(Ak::url_get_contents(rtrim($uri, '/').'/'.$name.'/CHANGELOG')) == md5_file(PLUGINS_DIR.DS.$name.DS.'CHANGELOG')){
            return false;
        }
        $this->_copyRemoteDir(rtrim($uri, '/').'/'.$name.'/', PLUGINS_DIR);
    }


    private function _setExternals($items, $extras = '') {
        $externals = array();
        foreach ($items as $name => $uri){
            $externals[] = "$name ".rtrim($uri, '/');
        }
        $tmp_file = TMP_DIR.DS.Ak::uuid();
        $plugins_dir = PLUGINS_DIR;
        AkFileSystem::file_put_contents($tmp_file, join("\n", $externals));
        `svn propset $extras -q svn:externals -F "$tmp_file" "$plugins_dir"`;
        AkFileSystem::file_delete($tmp_file);
    }

    private function _uninstallExternals($name) {
        $externals = $this->_getExternals();
        unset($externals[$name]);
        $this->_setExternals($externals);
    }

    private function _getExternals() {
        if($this->_shouldUseSvnExternals()){
            $plugins_dir = PLUGINS_DIR;
            $svn_externals = array_diff(array_map('trim',(array)explode("\n", `svn propget svn:externals "$plugins_dir"`)), array(''));
            $externals = array();
            foreach ($svn_externals as $svn_external){
                list($name, $uri) = explode(' ', trim($svn_external));
                $externals[$name] = $uri;
            }
            return $externals;
        }else{
            return array();
        }
    }

    private function _installUsingHttp($name, $uri) {
        $this->_copyRemoteDir(rtrim($uri, '/').'/'.$name.'/', PLUGINS_DIR);
    }
}

