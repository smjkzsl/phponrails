<?php


/**
 * @package ActiveSupport
 * @subpackage Generators
 * @author Bermi Ferrer <bermi a.t bermilabs c.om>
  */


class MailerGenerator extends  RailsGenerator
{
    var $command_values = array('class_name','(array)actions');

    function _preloadPaths() {
        $this->class_name = AkInflector::camelize($this->class_name);
        $this->assignVarToTemplate('class_name', $this->class_name);
        $this->actions = Ak::toArray(@$this->actions);
        if(empty($this->actions)){
            trigger_error(Ak::t('You must supply at least one action for the mailer.'), E_USER_ERROR);
        }
        $this->assignVarToTemplate('actions', $this->actions);
        $this->underscored_class_name = AkInflector::underscore($this->class_name);
        $this->model_path = 'app'.DS.'models'.DS.$this->underscored_class_name.'.php';
        $this->installer_path = 'app'.DS.'installers'.DS.$this->underscored_class_name.'_installer.php';
    }

    function hasCollisions() {
        $this->_preloadPaths();
        
        $this->collisions = array();

        $files = array(
        AkInflector::toModelFilename($this->class_name),
        TEST_DIR.DS.'unit'.DS.'app'.DS.'models'.DS.$this->underscored_class_name.'.php'
        );
        
        foreach ($this->actions as $action){
            $files[] = VIEWS_DIR.DS.AkInflector::underscore($this->class_name).DS.$action.'.tpl';
        }

        foreach ($files as $file_name){
            if(file_exists($file_name)){
                $this->collisions[] = Ak::t('%file_name file already exists',array('%file_name'=>$file_name));
            }
        }
        return count($this->collisions) > 0;
    }

    function generate() {
        $this->_preloadPaths();

        $this->class_name = AkInflector::camelize($this->class_name);

        $files = array(
        'mailer'=>AkInflector::toModelFilename($this->class_name),
        'unit_test'=>TEST_DIR.DS.'unit'.DS.'app'.DS.'models'.DS.$this->underscored_class_name.'.php'
        );

        foreach ($files as $template=>$file_path){
            $this->save($file_path, $this->render($template));
        }

        $mailer_views_folder = VIEWS_DIR.DS.AkInflector::underscore($this->class_name);
        @AkFileSystem::make_dir($mailer_views_folder);

        foreach ($this->actions as $action){
            $this->assignVarToTemplate('action', $action);
            $path = $mailer_views_folder.DS.$action.'.tpl';
            $this->assignVarToTemplate('path', $path);
            $this->save($path, $this->render('view'));
        }
    }
}

?>
