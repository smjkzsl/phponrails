<?php

ak_define('ACTIVE_RECORD_VALIDATE_TABLE_NAMES', false);

class ScaffoldGenerator extends  RailsGenerator
{
    var $_skip_files = array();
    var $command_values = array('model_name','controller_name','(array)table_columns');

    function cast() {
        $this->model_name = AkInflector::camelize($this->model_name);
        $this->model_file_path = AkInflector::toModelFilename($this->model_name);
        if(empty($this->actions) && !empty($this->controller_name) && strstr($this->controller_name, ',')){
            $this->controller_name = '';
        }
        $this->table_columns =str_replace(":"," " , trim(join(', ', (array)@$this->table_columns)));
	$this->assignVarToTemplate('table_columns', $this->table_columns);
	
        $this->controller_name = empty($this->controller_name) ? AkInflector::pluralize($this->model_name) : (AkInflector::camelize($this->controller_name));
        $this->controller_file_path = AkInflector::toControllerFilename($this->controller_name);
        $this->controller_class_name = str_replace(array('/','::'),'_', $this->controller_name.'Controller');
        $this->controller_name = AkInflector::demodulize($this->controller_name);
        $this->controller_human_name = AkInflector::humanize($this->controller_name);
        $this->helper_name = (AkInflector::is_plural($this->controller_name)?AkInflector::singularize($this->controller_name):$this->controller_name).'Helper';
        $this->helper_var_name = '$'.AkInflector::underscore($this->helper_name);

        $this->singular_name = AkInflector::underscore($this->model_name);
        $this->plural_name = AkInflector::pluralize($this->singular_name);
        $this->singular_controller_name = AkInflector::underscore($this->controller_name);

        $this->module_prefix = AkInflector::underscore(substr($this->controller_class_name, 0, strrpos($this->controller_class_name, '_')));
        $this->module_prefix = empty($this->module_prefix) ? '' : DS.$this->module_prefix;


        $this->files = array(
        'controller.php' => $this->controller_file_path,
        /**
         * @todo Implement generic functional tests
         */
        // 'functional_test.php' => TEST_DIR.DS.'functional'.DS.'test_'.$this->controller_class_name.'.php',
        'helper.php' => HELPERS_DIR.$this->module_prefix.DS.trim($this->helper_var_name,'$').'.php',
        'layout' => VIEWS_DIR.DS.'layouts'.DS.$this->singular_controller_name.'.tpl',
        'view_add' => VIEWS_DIR.$this->module_prefix.DS.$this->singular_controller_name.DS.'add.tpl',
        'view_destroy' => VIEWS_DIR.$this->module_prefix.DS.$this->singular_controller_name.DS.'destroy.tpl',
        'view_edit' => VIEWS_DIR.$this->module_prefix.DS.$this->singular_controller_name.DS.'edit.tpl',
        'view_listing' => VIEWS_DIR.$this->module_prefix.DS.$this->singular_controller_name.DS.'listing.tpl',
        'view_show' => VIEWS_DIR.$this->module_prefix.DS.$this->singular_controller_name.DS.'show.tpl',
        'form' => VIEWS_DIR.$this->module_prefix.DS.$this->singular_controller_name.DS.'_form.tpl',
        );
/*
        $this->user_actions = array();
        foreach ((array)@$this->actions as $action){
            $this->user_actions[$action] = VIEWS_DIR.$this->module_prefix.DS.$this->singular_controller_name.DS.$action.'.tpl';
        }
*/
    }

    function hasCollisions() {
        $this->collisions = array();
        foreach (array_values($this->files) as $file_name){
            $user_answer = 5;
            if($user_answer != 3 && file_exists($file_name)){
                $message = Ak::t('%file_name file already exists',array('%file_name'=>$file_name));
                $user_answer = AkConsole::promptUserVar($message."\n".
                "Would you like to:\n".
                " 1) overwrite file\n".
                " 2) keep existing file\n".
                " 3) overwrite all\n".
                " 4) keep all\n".
                " 5) abort\n", array('default' => 5));
                
                if($user_answer == 2 || $user_answer == 4){
                    $this->_skip_files[] = $file_name;
                }elseif($user_answer == 5){
                    $this->collisions[] = $message;
                }
            }
        }
        return count($this->collisions) > 0;
    }

    function generate() {
        //Generate models if they don't exist
        $model_files = array(
        'model'             => $this->model_file_path,
        'installer'         => AkConfig::getDir('app').DS.'installers'.DS.$this->singular_name.'_installer.php',
        'model_unit_test'   => TEST_DIR.DS.'unit'.DS.'app'.DS.'models'.DS.$this->singular_name.'.php',
        'model_fixture'     => TEST_DIR.DS.'fixtures'.DS.'app'.DS.'models'.DS.$this->singular_name.'.php',
        'installer_fixture' => TEST_DIR.DS.'fixtures'.DS.'app'.DS.'installers'.DS.$this->singular_name.'_installer.php'
        );

        $this->_template_vars = (array)$this;
        foreach ($model_files as $template=>$file_path){
            if(!file_exists($file_path)){
                $this->save($file_path, $this->render($template, !empty($this->sintags)));
            }
        }

        if(file_exists($this->model_file_path)){
            require_once($this->model_file_path);
            if(class_exists($this->model_name)){
                $ModelInstance = new $this->model_name;
                $table_name = $ModelInstance->getTableName();
                if(!empty($table_name)){
                    $this->content_columns = $ModelInstance->getContentColumns();
                    unset(
                    $this->content_columns['updated_at'],
                    $this->content_columns['updated_on'],
                    $this->content_columns['created_at'],
                    $this->content_columns['created_on']
                    );
                }
                $internationalized_columns = $ModelInstance->getInternationalizedColumns();
                foreach ($internationalized_columns as $column_name=>$languages){
                    foreach ($languages as $lang){
                        $this->content_columns[$column_name] = $this->content_columns[$lang.'_'.$column_name];
                        $this->content_columns[$column_name]['name'] = $column_name;
                        unset($this->content_columns[$lang.'_'.$column_name]);
                    }
                }
            }
        }

        $this->_template_vars = (array)$this;
        foreach ($this->files as $template=>$file_path){
            if(!in_array($file_path,$this->_skip_files)){
                $this->save($file_path, $this->render($template, !empty($this->sintags)));
            }
        }
        /*foreach ($this->user_actions as $action=>$file_path){
            $this->assignVarToTemplate('action',$action);
            $this->save($file_path, $this->render('view', !empty($this->sintags)));
        }*/
    }
}

?>