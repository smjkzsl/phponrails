<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
//控制台运行mrails 耒定�?BASE_DIR
if(!defined('BASE_DIR')){
    $__ak_base_dir = array_slice(get_included_files(),-2,1);
    $__ak_base_dir = dirname($__ak_base_dir[0]);
    if(is_dir($__ak_base_dir.DS.'app_layout')){
        defined('RAILS_DIR') || define('RAILS_DIR', $__ak_base_dir);
        defined('TEST_DIR')      || define('TEST_DIR', RAILS_DIR.DS.'test');
        defined('CORE_DIR')      || define('CORE_DIR', RAILS_DIR);
        define('SKIP_ENV_CONFIG', false);
        $__ak_base_dir .= DS.'app_layout';
    }
    define('BASE_DIR', $__ak_base_dir);
    unset($__ak_base_dir);
    defined('SKIP_ENV_CONFIG') || define('SKIP_ENV_CONFIG', true);
}

defined('MRAILS_BASE_DIR') || define('MRAILS_BASE_DIR', BASE_DIR);
defined('MRAILS_RUN')      || define('MRAILS_RUN', preg_match('/mrails$/', $_SERVER['PHP_SELF']));

class MrailsRequest
{
    public
    $attributes,
    $tasks,
    $constants = array();

    public function __construct() {
        if(php_sapi_name() == 'cli'){
            $this->useCommandLineArguments();
        }
    }

    public function useCommandLineArguments() {
        $arguments = $GLOBALS['argv'];
        array_shift($arguments);
        $this->parse($arguments);
    }

    public function parse($arguments) {
        $task_set = false;
        $argv = $arguments;
        while(!empty($arguments)){
            $argument = array_shift($arguments);
            if($argument == '--hide-mrails-folder'){
                define('MRAILS_SHOW_FOLDER', false);
                continue;
            }
            /**
             *  Captures assignments even if there are blank spaces before or after the equal symbol.
             */
            if(isset($arguments[0][0]) && $arguments[0][0] == '='){
                $argument .= $arguments[0];
                array_shift($arguments);
            }
            if(preg_match('/^
                                (-{0,2})
                                (
                                     (?![\w\d\.\-_:\/\\\]+\/\/) # If its not a protocol
                                        [\w\d\.\-_:\/\\\]+\s?
                                )
                                (=?)
                                (\s?.*)
                            $/x', $argument, $matches)){
                $constant_or_attribute = ((strtoupper($matches[2]) === $matches[2]) ? 'constants' : 'attributes');
                $is_constant = $constant_or_attribute == 'constants';

                if(($matches[3] == '=' || ($matches[3] == '' && $matches[4] != ''))){
                    $matches[4] = ($matches[4] === '') ? array_shift($arguments) : $matches[4];
                    if(!empty($task) && !$is_constant){
                        $this->tasks[$task]['attributes'][trim($matches[2], ' :')] = $this->_castValue(trim($matches[4], ' :'));
                    }else{
                        $this->{$constant_or_attribute}[trim($matches[2], ' :')] = $this->_castValue(trim($matches[4], ' :'));
                    }
                }elseif(!$task_set && (empty($matches[1]) || $matches[1] != '-')){
                    $task = trim($matches[2], ' :');
                    $this->tasks[$task] = array();
                    $task_set = true;
                }elseif($matches[1] == '-' && isset($task)){
                    foreach (str_split($matches[2]) as $k){
                        $this->tasks[$task]['attributes'][$k] = true;
                    }
                }elseif($task_set && isset($task)){
                    if($matches[1] == '--'){
                        $this->tasks[$task]['attributes'][trim($matches[2], ' :')] = true;
                    }else{
                        $this->tasks[$task]['attributes'][trim($matches[0], ' :')] = $this->_castValue(trim($matches[0], ' :'));
                    }
                }
            }elseif ($task_set) {
                $this->tasks[$task]['attributes'][trim($argument, ' :')] = $this->_castValue(trim($argument, ' :'));
            }
        }
    }

    public function get($name, $type = null) {
        if(!empty($type)){
            return isset($this->{$type}[$name]) ? $this->{$type}[$name] : false;
        }else{
            foreach (array('constants', 'attributes') as $type){
                return $this->get($name, $type);
            }
        }
    }

    public function flag($name) {
        return $this->get($name, __FUNCTION__);
    }

    public function constant($name) {
        return $this->get($name, __FUNCTION__);
    }

    public function attribute($name) {
        return $this->get($name, __FUNCTION__);
    }

    public function defineConstants() {
        foreach ($this->constants as $constant => $value){
            if(!preg_match('/^/', $constant)){
                define(''.$constant, $value);
            }
            define($constant, $value);
        }
    }

    private function _castValue($value) {
        if(in_array($value, array(true,1,'true','True','TRUE','1','y','Y','yes','Yes','YES'), true)){
            return true;
        }
        if(in_array($value, array(false,0,'false','False','FALSE','0','n','N','no','No','NO'), true)){
            return false;
        }
        return $value;
    }
}

$MrailsRequest = new MrailsRequest();

if(MRAILS_RUN){
    // Setting constants from arguments before including configurations
    $MrailsRequest->defineConstants();

    $_config_file = BASE_DIR.DS.'config'.DS.'config.php';

    if(!is_file($_config_file) || !include_once($_config_file)){
        defined('ENVIRONMENT')   || define('ENVIRONMENT', 'testing');
        if(!is_file(BASE_DIR.DS.'config'.DS.'environment.php') || !include(BASE_DIR.DS.'config'.DS.'environment.php')){
            defined('SKIP_ENV_CONFIG') || define('SKIP_ENV_CONFIG', true);
            include BASE_DIR.DS.'autoload.php';
        }
    }

    Ak::setStaticVar('dsn', $dsn);
    defined('RECODE_UTF8_ON_CONSOLE_TO') ? null : define('RECODE_UTF8_ON_CONSOLE_TO', false);

    ini_set('memory_limit', -1);
    set_time_limit(0);
    //error_reporting(E_ALL);
}

class Mrails
{
    public $tasks = array();
    public $task_files = array();
    public $task_paths = array(TASKS_DIR);
    public $current_task;
    public $settings = array(
    'app_name' => 'Rails application name'
    );
    public $Request;
    public $Installer;

    public function __construct(&$Request) {
        $this->Request = $Request;
        $this->Installer = new AkInstaller();
        $this->makefiles = $this->getMakeFiles();
    }

    public function loadMakefiles() {
        foreach ($this->makefiles as $makefile){
            if(file_exists($makefile)){
                include($makefile);
            }
        }
    }

    public function runTasks() {
        if(isset($this->Request->tasks['mrails:autocomplete'])){
            $this->runTask('mrails:autocomplete', $this->Request->tasks['mrails:autocomplete'], false);
            return;
        }
        if(!defined('MRAILS_SHOW_FOLDER') || MRAILS_SHOW_FOLDER){
            $this->message('(in '.MRAILS_BASE_DIR.')');
        }
        if(!empty($this->Request->tasks)){
            foreach ($this->Request->tasks as $task => $arguments){
                $this->runTask($task, $arguments);
            }
        }else{
            $this->runTask('T', array());
        }
    }

    public function runTask($task_name, $options = array(), $only_registered_tasks = true) {
        $this->removeAutocompletionOptions($task_name);
        if(!empty($this->tasks[$task_name]['with_defaults'])){
            $attributes = isset($options['attributes']) ? (array)$options['attributes'] : array();
            $options['attributes'] = array_merge((array)$this->tasks[$task_name]['with_defaults'], $attributes);
            unset($this->tasks[$task_name]['with_defaults']);
        }
        if(!empty($options['attributes']['daemon'])){
            unset($options['attributes']['daemon']);
            $this->runTaskAsDaemon($task_name, $options);
            return;
        }elseif(!empty($options['attributes']['background'])){
            unset($options['attributes']['background']);
            $this->runTaskInBackground($task_name, $options);
            return;
        }
        $this->current_task = $task_name;
        if($only_registered_tasks && !isset($this->tasks[$task_name])){
            if(!$this->showBaseTaskDocumentation($task_name)){
                $this->error("\nInvalid task $task_name, use \n\n   $ ./mrails -T\n\nto show available tasks.\n");
            }
        }else{
            $parameters = isset($this->tasks[$task_name]['parameters']) ? $this->tasks[$task_name]['parameters'] : array();
            $attributes = isset($options['attributes']) ? (array)$options['attributes'] : array();
            $parameters = $this->getParameters($parameters, $attributes);
            $this->runTaskFiles($task_name, $parameters);
            $snippets = isset($this->tasks[$task_name]['run']) ? $this->tasks[$task_name]['run'] : '';
            $this->runTaskCode($snippets, $parameters);
        }
    }



    public function showBaseTaskDocumentation($task_name) {
        $success = false;
        $this->message(' ');
        foreach ($this->tasks as $task => $details){
            if(preg_match("/^$task_name/", $task)){
                $this->showTaskDocumentation($task);
                $success = true;
            }
        }
        return $success;
    }

    public function showTaskDocumentation($task) {
        $this->message(sprintf("%-30s",$task).'  '.(isset($this->tasks[$task]['description'])?$this->tasks[$task]['description']:''));
    }

    public function run($task_name, $options = array()) {
        return $this->runTask($task_name, $options);
    }

    public function runTaskCode($code_snippets = array(), $options = array()) {
        if(!empty($code_snippets)){
            foreach ((array)$code_snippets as $language => $code_snippets){
                $code_snippets = is_array($code_snippets) ? $code_snippets : array($code_snippets);
                $language_method = AkInflector::camelize('run_'.$language.'_snippet');

                if(method_exists($this, $language_method)){
                    foreach ($code_snippets as $code_snippet){
                        $this->$language_method($code_snippet, $options);
                    }
                }else{
                    $this->error("Could not find a handler for running $language code on $this->current_task task", true);
                }
            }
        }
    }

    public function runTaskFiles($task_name, $options = array()) {
        $files = $this->_getTaskFiles($task_name);
        $task_name = str_replace(':', DS, $task_name);
        $Mrails = $this;
        $Logger = Ak::getLogger('mrails'.DS.AkInflector::underscore($task_name));
        foreach ($files as $file) {
            $pathinfo = pathinfo($file);
            if(isset($pathinfo['extension']) && $pathinfo['extension'] == 'php'){
                include($file);
            }else{
                echo `$file`;
            }
        }
    }

    public function getParameters($parameters_settings, $request_parameters) {
        $parameters_settings = Ak::toArray($parameters_settings);

        if(empty($parameters_settings)){
            return $request_parameters;
        }
        $parameters = array();
        foreach ($parameters_settings as $k => $v){
            $options = array();
            $required = true;
            if(is_numeric($k)){
                $parameter_name = $v;
            }else{
                $parameter_name = $k;
                if(is_array($v) && !empty($v['optional'])){
                    $required = false;
                    unset($v['optional']);
                }
            }
            if($required && !isset($request_parameters[$parameter_name])){
                $this->error("\nMissing \"$parameter_name\" parameter on $this->current_task\n", true);
            }
        }
    }

    public function runPhpSnippet($code, $options = array()) {
        $fn = create_function('$options, $Mrails', $code.';');
        return $fn($options, $this);
    }

    public function runSystemSnippet($code, $options = array()) {
        $code = trim($code);
        return $this->message(`$code`);
    }

    public function defineTask($task_name, $options = array()) {
        $default_options = array();
        $task_names = strstr($task_name, ',') ? array_map('trim', explode(',', $task_name)) : array($task_name);
        foreach ($task_names as $task_name) {
            $this->tasks[$task_name] = $options;
        }
    }

    public function addSettings($settings) {
        $this->settings = array_merge($this->settings, $settings);
    }

    public function displayAvailableTasks() {

        $this->message("\nYou can perform taks by running:\n");
        $this->message("    ./mrails task:name");
        $this->message("\nOptionally you can define contants or pass attributes to the tasks:\n");
        $this->message("    ./mrails task:name ENVIROMENT=production parameter=value param -abc --param=value");

        $this->message("\nShowing tasks avalable at ".TASKS_DIR.":\n");

        ksort($this->tasks);

        foreach ($this->tasks as $task => $details){
            $this->showTaskDocumentation($task);
        }
    }


    public function error($message, $fatal = false) {
        $this->message($message);
        if($fatal){
            die();
        }
    }
    public function message($message) {
        if(!empty($message)){
            echo $message."\n";
        }
    }

    public function runTaskInBackground($task_name, $options = array()) {
        $this->_ensurePosixAndPcntlAreAvailable();
        $pid = Ak::pcntl_fork();
        if($pid == -1){
            $this->error("Could not run background task.\n Call with --background=false to avoid backgrounding.", true);
        }elseif($pid == 0){
            $dsn = Ak::getStaticVar('dsn');
            defined('SKIP_DB_CONNECTION') && SKIP_DB_CONNECTION ? null : Ak::db($dsn);
            $this->runTask($task_name, $options);
            posix_kill(getmypid(),9);
        }else{
            $this->message("\nRunning background task $task_name with pid $pid");
        }
    }


    public function runTaskAsDaemon($task_name, $options = array()) {
        $this->_ensurePosixAndPcntlAreAvailable();

        require_once 'System/Daemon.php';

        $app_name = AkInflector::underscore($task_name);
        $pid_file = BASE_DIR.DS.'run'.DS.$app_name.DS.$app_name.'.pid';
        $log_file = LOG_DIR.DS.'daemons'.DS.$app_name.'.log';

        if(!file_exists($pid_file)){
            if(empty($options['attributes']['kill'])){
                AkFileSystem::file_put_contents($pid_file, '');
                AkFileSystem::file_delete($pid_file);
            }else{
                $this->error("Could not kill process for $task_name", true);
            }
        }else{
            $pid = (int)file_get_contents($pid_file);
            if($pid > 0){
                if(!empty($options['attributes']['kill'])){
                    $this->message("Killing process $pid");
                    `kill $pid`;
                    AkFileSystem::file_delete($pid_file);
                    die();
                }elseif(!empty($options['attributes']['restart'])){
                    $this->message("Restarting $task_name.");
                    $this->message(`kill $pid`);
                }else{
                    $this->error("Daemon for $task_name still running ($pid_file).\nTask aborted.", true);
                }
            }
        }

        if(!empty($options['attributes']['kill']) && empty($pid)){
            $this->error("No daemon running for task $task_name", true);
        }
        unset($options['attributes']['restart']);

        if(!file_exists($log_file)){
            AkFileSystem::file_put_contents($log_file, '');
        }

        System_Daemon::setOption('appName', $app_name);
        System_Daemon::setOption('appDir', BASE_DIR);
        System_Daemon::setOption('logLocation', $log_file);
        System_Daemon::setOption('appRunAsUID', posix_geteuid());
        System_Daemon::setOption('appRunAsGID', posix_getgid());
        System_Daemon::setOption('appPidLocation', $pid_file);
        $this->message("Staring daemon. ($log_file)");
        System_Daemon::start();
        $dsn = Ak::getStaticVar('dsn');
        defined('SKIP_DB_CONNECTION') && SKIP_DB_CONNECTION ? null : Ak::db($dsn);
        $this->runTask($task_name, $options);
        System_Daemon::stop();
        AkFileSystem::file_delete($pid_file);
        die();
    }


    // Autocompletion handling

    public function getAvailableTasksForAutocompletion() {
        return array_keys($this->tasks);
    }

    public function getAutocompletionOptionsForTask($task, $options = array(), $level = 1) {
        $task_name = str_replace(':', DS, $task);
        $Mrails = $this;
        $autocompletion_options = array();
        $autocomplete_accessor = 'autocompletion'.($level === 1 ? '' : '_'.$level);
        $autocompletion_executables = $this->multiGlob(array(
        $task_name.'*.'.$autocomplete_accessor.'.*',
        $task_name.DS.$task_name.'.'.$autocomplete_accessor.'.*'
        ));

        if(!empty($autocompletion_executables)){
            ob_start();
            foreach ($autocompletion_executables as $file){
                $pathinfo = pathinfo($file);
                if(isset($pathinfo['extension']) && $pathinfo['extension'] == 'php'){
                    include($file);
                }else{
                    echo `$file`;
                }
            }
            echo "\n";
            $autocompletion_options = array_diff(explode("\n", ob_get_clean()), array(''));
        }
        $autocomplete_accessor = 'autocompletion'.($level === 1 ? '' : '_'.$level);
        if(isset($this->tasks[$task][$autocomplete_accessor])){
            $autocompletion_options = array_merge(Ak::toArray($this->tasks[$task][$autocomplete_accessor]), $autocompletion_options);
        }
        array_unique($autocompletion_options);
        $autocompletion_options = array_diff($autocompletion_options, array_merge(array($task), $options));
        return $autocompletion_options;
    }

    public function removeAutocompletionOptions($task_name) {
        if (!empty($this->tasks[$task_name])) {
            foreach ($this->tasks[$task_name] as $k => $v){
                if(preg_match('/^autocompletion/', $k)){
                    unset($this->tasks[$task_name][$k]);
                }
            }
        }
    }

    public function getMakeFiles(){
        return $this->multiGlob(array(
        'makefile.php',
        '*/makefile.php',
        '*/*/makefile.php',
        '*/*/*/makefile.php',
        '*/*/*/*/makefile.php'));
    }

    public function multiGlob($patterns = array(), $task_path = null){
        $task_paths = empty($task_path) ? AkConfig::getOption('mrails_task_paths', array_merge($this->task_paths, array(dirname(__FILE__).DS.'tasks'))) : (array)$task_path;

        $glob_result = array();
        foreach ($patterns as $pattern){
            foreach ($task_paths as $task_path){
                $glob_result = array_merge($glob_result, glob($task_path.DS.$pattern));
            }
        }
        array_unique($glob_result);
        return array_diff($glob_result, array(''));
    }


    protected function _getTaskFiles($task_name, $bark_on_error = true){
        $task_parts = array_diff(explode(':', $task_name.':'), array(''));
        $task_part_count = count($task_parts);
        $search_patterns = array();

        $search_patterns[] = join(DS, $task_parts).'.task*.*';
        if($task_part_count == 1){
            $search_patterns[] = str_replace(':',DS, $task_name).DS.join(DS, $task_parts).'.task*.*';
        }
        $subtask = array_pop($task_parts);
        if(!empty($subtask)){
            $search_patterns[] = join(DS, $task_parts).'.'.$subtask.'.task*.*';
            if($task_part_count == 1){
                $search_patterns[] = str_replace(':',DS, $task_name).DS.join(DS, $task_parts).'.'.$subtask.'.task*.*';
            }
        }
        $task_files = $this->multiGlob($search_patterns);

        //
        $task_files = array_diff($task_files, array(''));
        if($bark_on_error && empty($this->tasks[$task_name]['run']) && empty($task_files)){
            $this->error("No task file found for $task_name in ".TASKS_DIR, true);
        }
        return $task_files;
    }


    private function _ensurePosixAndPcntlAreAvailable() {
        if(!function_exists('posix_geteuid')){
            trigger_error('POSIX functions not available. Please compile PHP with --enable-posix', E_USER_ERROR);
        }elseif(!function_exists('pcntl_fork')){
            trigger_error('pcntl functions not available. Please compile PHP with --enable-pcntl', E_USER_ERROR);
        }
    }

}

Ak::setStaticVar('Mrails', new Mrails($MrailsRequest));

function mrails_task($task_name, $options = array()){
    Ak::getStaticVar('Mrails')->defineTask($task_name, $options);
}

function mrails_setting($settings = array()){
    Ak::getStaticVar('Mrails')->addSettings($settings);
}


/**
 * @todo
 *
 *  Task
 *      prequisites
 *      actions
 *      expected parameters
 *
 *
 *  Directory functions
 *  Parallel tasks
 *

 ./mrails db:fixtures:load         # Load fixtures into the current environment&#8217;s database.
                                    # Load specific fixtures using FIXTURES=x,y
./mrails db:migrate                # Migrate the database through scripts in db/migrate. Target
                                    # specific version with VERSION=x
./mrails db:structure:dump         # Dump the database structure to a SQL file
./mrails db:test:clone             # Recreate the test database from the current environment&#8217;s
                                    # database schema
./mrails db:test:clone_structure   # Recreate the test databases from the development structure
./mrails db:test:prepare           # Prepare the test database and load the schema
./mrails db:test:purge             # Empty the test database

./mrails doc:app                   # Build the app documetation Files into docs/app/api
./mrails doc:plugins               # Generate documation for all installed plugins in docs/plugins
./mrails doc:rails                # Build the rails documentation files into docs/rails/api
./mrails doc:website               # Add a new controller at /docs to browse avaliable documentation
./mrails doc:website:remove        # Removed the files added by ./mrails doc:website

./mrails log:clear                 # Truncates all *.log files in log/ to zero bytes

./mrails rails:update             # Update both scripts and public/javascripts from Rails
./mrails rails:update:javascripts # Update your javascripts from your current rails install
./mrails rails:update:scripts     # Add new scripts to the application script/ directory

./mrails stats                     # Report code statistics (KLOCs, etc) from the application

./mrails test                      # Test all units and functionals
./mrails test:functionals          # Run tests for functionals db:test:prepare
./mrails test:integration          # Run tests for integrationdb:test:prepare
./mrails test:plugins              # Run tests for pluginsenvironment
./mrails test:recent               # Run tests for recentdb:test:prepare
./mrails test:uncommitted          # Run tests for uncommitteddb:test:prepare
./mrails test:units                # Run tests for unitsdb:test:prepare

./mrails tmp:cache:clear           # Clears all files and directories in tmp/cache
./mrails tmp:clear                 # Clear session, cache, and socket files from tmp/
./mrails tmp:create                # Creates tmp directories for sessions, cache, and sockets
./mrails tmp:sessions:clear        # Clears all files in tmp/sessions
./mrails tmp:sockets:clear         # Clears all ruby_sess.* files in tmp/sessions
 */

if(MRAILS_RUN){
    Ak::getStaticVar('Mrails')->loadMakefiles();
    Ak::getStaticVar('Mrails')->runTasks();
    echo "\n";
}
