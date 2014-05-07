<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class RailsGenerator
{
    public $log = array();
    public $type = '';
    public $_template_vars = array();
    public $collisions = array();
    public $generators_dir = GENERATORS_DIR;
    private $_file_options = array();

    public function runCommand($command) {
        $commands = $this->getOptionsFromCommand($command);
        $generator_name = AkInflector::underscore(isset($commands['generator']) ? $commands['generator'] : array_shift($commands));

        $available_generators = $this->getAvailableGenerators();
        $generator_file_name = Ak::first(array_keys($available_generators, $generator_name));

        if(empty($generator_file_name)){
            echo "\n   ".Ak::t("You must supply a valid generator as the first command.\n".
                 "          (i.e. # ./mrails generate controller)\n\n   Available generator are:");
            echo "\n\n   ".join("\n   ", $available_generators)."\n\n";
            defined('CONSOLE_MODE') && CONSOLE_MODE ? null : exit;
            return ;
        }

        if(include_once($generator_file_name)){

            $generator_class_name = AkInflector::camelize($generator_name.'_generator');
            $generator = new $generator_class_name();
            $generator->setFileOptions($this->getFileOptions());
            $generator->_generator_base_path = dirname($generator_file_name);

            if(count(array_diff($commands,array('help','-help','--help','usage','-usage','h','-h','USAGE','-USAGE'))) != count($commands) || count($commands) == 0){
                if(empty($generator->command_values) && empty($commands)){
                    // generator without commands
                }else{
                    $generator->banner();
                    return;
                }
            }

            $generator->type = $generator_name;
            $generator->_identifyUnnamedCommands($commands);
            $generator->_assignVars($commands);
            $generator->cast();
            $generator->_generate();
        }else {
            echo "\n".Ak::t('Could not find %generator_name generator',array('%generator_name'=>$generator_name))."\n";
        }
    }

    public function _assignVars($template_vars) {
        foreach ($template_vars as $key=>$value){
            $this->$key = $value;
        }
        $this->_template_vars = (array)$this;
    }

    public function assignVarToTemplate($var_name, $value) {
        $this->_template_vars[$var_name] = $value;
    }

    public function cast() {

    }

    public function render($template, $sintags_version = false) {
        $__file_path = $this->getGeneratorDir().DS.$this->type.DS.($sintags_version?'sintags_':'').'templates'.DS.(strstr($template,'.') ? $template : $template.'.tpl');

        if(!file_exists($__file_path)){
            trigger_error(Ak::t('Template file %path not found.', array('%path'=>$__file_path)), E_USER_NOTICE);
        }
        extract($this->_template_vars);
        ob_start();
        include($__file_path);
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }
    
    public function getGeneratorDir(){
        foreach (array_reverse(get_included_files()) as $file){
            if(strstr($file, DS.$this->type.DS.$this->type.'_generator.php')){
                return str_replace(DS.$this->type.DS.$this->type.'_generator.php', '' , $file);
            }
        }
        return $this->generators_dir;
    }

    public function save($file_path, $content) {
        $this->log[] = $file_path;
        AkFileSystem::file_put_contents($file_path, $content, $this->getFileOptions());
    }

    public function printLog() {
        if(!empty($this->log)){
            echo "\n".Ak::t('The following files have been created:')."\n";
            echo join("\n",$this->log)."\n";
        }
        $this->log = array();
    }

    public function hasCollisions() {
        $this->collisions = array();
        foreach (array_keys($this->getFilePaths()) as $file_name){
            if(file_exists($file_name)){
                $this->collisions[] = Ak::t('%file_name file already exists',array('%file_name'=>$file_name));
            }
        }
        return count($this->collisions) > 0;
    }
    
    /**
     * @return array with the file path and the template to be rendered.
     */
    public function getFilePaths(){
        return array();
    }

    public function getOptionsFromCommand($command) {
        $command = $this->_maskAmpersands($command);


        // Named params
        if(preg_match_all('/( ([A-Za-z0-9_-])+=)/',' '.$command,$result)){
            $command = str_replace($result[0],$this->_addAmpersands($result[0]),$command);
            if(preg_match_all('/( [A-Z-a-z0-9_-]+&)+/',' '.$command,$result)){
                $command = str_replace($result[0],$this->_addAmpersands($result[0]),$command);
            }
        }
        $command = join('&',array_diff(explode(' ',$command.' '),array('')));

        parse_str($command,$command_pieces);

        $command_pieces = array_map('stripslashes',$command_pieces);
        $command_pieces = array_map(array($this,'_unmaskAmpersands'),$command_pieces);

        $params = array();
        foreach ($command_pieces as $param=>$value){
            if(empty($value)){
                $params[] = $param;
            }else{
                $param = $param[0] == '-' ? substr($param,1) : $param;
                $params[$param] = trim($value,"\"\n\r\t");
            }
        }
        return $params;
    }

    public function manifest($call_generate = true) {
        return $call_generate ? $this->generate() : null;
    }

    public function generate() {
        return $this->manifest(false);
    }
    
    public function banner() {
        $usage = @file_get_contents(@$this->_generator_base_path.DS.'USAGE');
        echo empty($usage) ? "\n".Ak::t('Could not locate usage file for this generator') : "\n".$usage."\n";
    }
    
    public function generateFromFilePaths() {
        foreach ($this->getFilePaths() as $file_path => $template){
            $this->assignVarToTemplate('path', str_replace(AkConfig::getDir('base'), '', $file_path));
            $this->assignVarToTemplate('file_path', $file_path);
            $this->save($file_path, $this->render($template));
        }
    }
    
    public function getAvailableGenerators() {
        return array_merge(
            $this->_getGeneratorsInsidePath($this->generators_dir), 
            $this->_getPluginGenerators(), 
            $this->_getApplicationGenerators(), 
            $this->_getExtraGenerators());
    }

    public function getFileOptions()
    {
        return $this->_file_options;
    }

    public function setFileOptions($options)
    {
        $this->_file_options = $options;
    }


    private function _identifyUnnamedCommands(&$commands) {
        $i = 0;
	    //~ var $command_values = array('model_name','controller_name','(array)actions');

        $extra_commands = array();
        $unnamed_commands = array();
        foreach ($commands as $param=>$value){
            if($value[0] == '-'){
                $next_is_value_for = trim($value,'- ');
                $extra_commands[$next_is_value_for] = true;
                continue;
            }

            if(isset($next_is_value_for)){
                $extra_commands[$next_is_value_for] = trim($value,'- ');
                unset($next_is_value_for);
                continue;
            }

            if(is_numeric($param)){
                if(!empty($this->command_values[$i])){
                    $index =$this->command_values[$i];
                    if(substr($this->command_values[$i],0,7) == '(array)'){
                        $index =substr($this->command_values[$i],7);
                        $unnamed_commands[$index][] = $value;
                        $i--;
                    }else{
                        $unnamed_commands[$index] = $value;
                    }
                }
                $i++;
            }
        }
        $commands = array_merge($extra_commands, $unnamed_commands);
    }
    
    private function _generate() {
        if(isset($this->_template_vars['force']) || !$this->hasCollisions()){
            $this->generate();
            $this->printLog();
        }else{
            echo "\n".Ak::t('There where collisions when attempting to generate the %type.',array('%type'=>$this->type))."\n";
            echo Ak::t('Please add --force to the argument list in order to overwrite existing files.')."\n\n";

            echo join("\n",$this->collisions)."\n";
        }
    }

    private function _addAmpersands($array) {
        $ret = array();
        foreach ($array as $arr){
            $ret[] = '&'.trim($arr);
        }
        return $ret;
    }

    private function _maskAmpersands($str) {
        return str_replace('&','___AMP___',$str);
    }

    private function _unmaskAmpersands($str) {
        return str_replace('___AMP___','&',$str);
    }

    private function _getPluginGenerators() {
        $generators = array();
        defined('PLUGINS_DIR') ? null : define('PLUGINS_DIR', AkConfig::getDir('app').DS.'vendor'.DS.'plugins');
        foreach (AkFileSystem::dir(PLUGINS_DIR,array('files'=>false,'dirs'=>true)) as $folder){
            $plugin_name = Ak::first(array_keys($folder));
            $generators = array_merge($generators, $this->_getGeneratorsInsidePath(PLUGINS_DIR.DS.$plugin_name.DS.'generators'));
        }
        return $generators;
    }
    
    private function _getApplicationGenerators() {
        return is_dir(APP_LIB_DIR.DS.'generators') ?
                $this->_getGeneratorsInsidePath(APP_LIB_DIR.DS.'generators') :
                array();
    }

    private function _getExtraGenerators() {
        $result = array();
        if($generator_paths = AkConfig::getOption('generator_paths', false)){
            foreach ($generator_paths as $generator_path){
                $result = array_merge($result, $this->_getGeneratorsInsidePath($generator_path));
            }
        }
        return $result;
    }

    private function _getGeneratorsInsidePath($path) {
        $generators = array();
        if(is_dir($path)){
            foreach (AkFileSystem::dir($path,array('files'=>false,'dirs'=>true)) as $folder){
                $generator = Ak::first(array_keys($folder));
                if(strstr($generator,'.php') || is_file($path.DS.$generator)){
                    continue;
                }
                $generators[$path.DS.$generator.DS.$generator.'_generator.php'] = $generator;
            }
        }
        return $generators;
    }
}

