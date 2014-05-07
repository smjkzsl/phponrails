<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkPhpTemplateHandler
{
    public $_options = array();
    public $_AkActionView;
    public $_templateEngine = DEFAULT_TEMPLATE_ENGINE;
    public $_codeSanitizerClass = PHP_CODE_SANITIZER_FOR_TEMPLATE_HANDLER;

    public function __construct(&$AkActionView) {
        $this->init($AkActionView);
    }

    public function init(&$AkActionView) {
        $this->_options = array();
        $this->_AkActionView = $AkActionView;
    }

    public function render(&$____code, $____local_assigns, $____file_path) {

        $this->_options['variables'] = $____local_assigns;
        $this->_options['code'] =& $____code;
        $this->_options['functions'] = array('');
        $this->_options['file_path'] = $____file_path;
        $_content='';
        if($this->_templateNeedsCompilation()){
            $TemplateEngine = $this->_getTemplateEngineInstance($this->_templateEngine);
            $TemplateEngine->init(array(
            'code' => $____code,
            'helper_loader' => $this->_AkActionView->getHelperLoader()
            ));

            $____code = $TemplateEngine->toPhp();

            if($____code === false){
                if(PRODUCTION_MODE){
                    trigger_error(join("\n",$TemplateEngine->getErrors()), E_USER_ERROR);
                    return false;
                }else{
                    trigger_error("Could not compile ".$this->_options['file_path']."\n\n".join("\n",$TemplateEngine->getErrors()), E_USER_ERROR);
                    echo highlight_string($TemplateEngine->getParsedCode(), true);
                    die();
                }
            }
            if(TEMPLATE_SECURITY_CHECK && $this->_templateNeedsValidation()){
                if(!$this->_assertForValidTemplate()){
                    return false;
                }
            }
            $_content=$____code;
            $this->_saveCompiledTemplate();
        }
        (array)$____local_assigns;
        extract($____local_assigns, EXTR_SKIP);
        ob_start();
        //include the code tmp
        if(IN_SAE){
            if(!$_content)
                $_content=SaeMCFS::content($this->_getCompiledTemplatePath());
            if (@(eval('?>' . $_content)) === false){
                $error = error_get_last();
                if (!is_null($error)) {
                    $file = strpos($error['file'], 'eval()') !== false ? SaeMCFS::$current_file : $error['file'];
                    trigger_error("<br /><b>template_error( {$this->_getCompiledTemplatePath()} )</b>:  {$error['message']} in <b>" . $file . "</b> on line <b>{$error['line']}</b><br />");
                }

            }
        }else{
            //if(IN_SAE)
             //   include SaeMCFS::saemcFileName($this->_getCompiledTemplatePath());
            //else
                include $this->_getCompiledTemplatePath();
        }

        empty($shared) || $this->_AkActionView->addSharedAttributes($shared);

        return  ob_get_clean();
    }

    private function &_getTemplateEngineInstance(){
        static $TemplateEngineInstances = array();
        if(!isset($TemplateEngineInstances[$this->_templateEngine])){
            if(!class_exists($this->_templateEngine)){
                require_once(ACTION_PACK_DIR.DS.'template_engines'.DS.$this->_templateEngine.DS.'base.php');
                $template_engine_name = 'Ak'.AkInflector::camelize($this->_templateEngine);
            }else{
                $template_engine_name = $this->_templateEngine;
            }
            $TemplateEngineInstances[$this->_templateEngine] = new $template_engine_name();
        }
        return $TemplateEngineInstances[$this->_templateEngine];
    }

    public function _assertForValidTemplate() {
        static $CodeSanitizer;
        if(empty($CodeSanitizer)){
            $class = $this->_codeSanitizerClass;
            $CodeSanitizer = new $class();
        }
        $CodeSanitizer->setOptions($this->_options);
        return $CodeSanitizer->isCodeSecure();
    }

    public function _templateNeedsCompilation() {
        if(IN_SAE){
            if(!SaeMCFS::file_exists($this->_getCompiledTemplatePath())|| FORCE_TEMPLATE_COMPILATION){
                return true;
            }
            $tpl_time = @filemtime($this->_getTemplatePath());
            $compiled_tpl_time = SaeMCFS::filemtime($this->_getCompiledTemplatePath());
        }else{
            if(!file_exists($this->_getCompiledTemplatePath()) || FORCE_TEMPLATE_COMPILATION){
                return true;
            }
            $tpl_time = @filemtime($this->_getTemplatePath());
            $compiled_tpl_time = filemtime($this->_getCompiledTemplatePath());
        }
        
        if($tpl_time > $compiled_tpl_time){
            return true;
        }
        return false;
    }

    public function _templateNeedsValidation() {
        return true;
    }

    public function _getTemplateBasePath() {
        if(empty($this->_options['template_base_path'])){
            $template_file_name = $this->_getTemplateFilename();
            if(!empty($template_file_name)){
                $file_path = str_replace(AkConfig::getDir('app'), APP_DIR,  $this->_options['file_path']);
                $this->_options['template_base_path'] = rtrim(str_replace($template_file_name,'',$file_path),'\/');
                if(COMPILED_VIEWS_DIR && !strstr($this->_options['template_base_path'], TMP_DIR)){
                    $this->_options['template_base_path'] = str_replace(BASE_DIR, COMPILED_VIEWS_DIR, $this->_options['template_base_path']);
                }
            }else{
                $this->_options['template_base_path'] = COMPILED_VIEWS_DIR;
            }
        }

        return $this->_options['template_base_path'];
    }


    public function _getTemplatePath() {
        return $this->_options['file_path'];
    }

    public function _getTemplateFilename() {
        $this->_options['template_filename'] = empty($this->_options['template_filename']) && preg_match('/[^\/^\\\]+$/',$this->_options['file_path'],$match) ? $match[0] : @$this->_options['template_filename'];
        return $this->_options['template_filename'];
    }

    public function _getCompiledTemplateBasePath() {
        if(empty($this->_options['compiled_template_base_path'])){
            $this->_options['compiled_template_base_path'] = $this->_getTemplateBasePath().DS.'compiled';
        }
        return $this->_options['compiled_template_base_path'];
    }

    public function _getCompiledTemplatePath() {
        if(empty($this->_options['compiled_file_name'])){
            $template_filename = $this->_getTemplateFilename();
            $this->_options['compiled_file_name'] =  $this->_getCompiledTemplateBasePath().DS.
            (empty($template_filename) ? 'tpl_'.md5($this->_options['code']) : $template_filename).'.'.
            $this->_getHelpersChecksum().'.php';
        }
        return $this->_options['compiled_file_name'];
    }

    public function _saveCompiledTemplate() {
       // if(IN_SAE){
         //   $options = array('base_path' =>'');
        //}else
             $options = array('base_path' => (COMPILED_VIEWS_DIR ? TMP_DIR : AkConfig::getDir('base')));
        if(defined('UPLOAD_FILES_USING_FTP') && UPLOAD_FILES_USING_FTP && !strstr($options['base_path'], AkConfig::getDir('base'))){
            $options['ftp'] = false;
        }
       if(IN_SAE){
            SaeMCFS::set($this->_getCompiledTemplatePath(), $this->_options['code']);
        } else{      
            AkFileSystem::file_put_contents($this->_getCompiledTemplatePath(), $this->_options['code'], $options);
        }
    }


    public function _getHelpersChecksum() {
        if(!isset($this->_helpers_checksum)){
            $this->_helpers_checksum = md5('v1'.serialize(AkHelperLoader::getInstantiatedHelperNames()));
        }
        return $this->_helpers_checksum;
    }
}

