<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class RailsPanel_RailsDashboardController  extends RailsPanelController
{
    public $app_helpers = 'rails_panel/docs';
    public function index(){
        $this->base_dir             = BASE_DIR;
        $this->rails_dir           = RAILS_DIR;
        $this->tasks_dir            = TASKS_DIR;
        $this->has_configuration    = file_exists(AkConfig::getDir('config').DS.'config.php');
        $this->has_routes           = file_exists(AkConfig::getDir('config').DS.'routes.php');
        $this->has_database         = file_exists(AkConfig::getDir('config').DS.'database.yml');
        $this->using_root_path      =   $this->Request->getPath() == '/';
        $this->new_install          =   !$this->has_configuration || !$this->has_routes || $this->using_root_path;
        $this->environment          = ENVIRONMENT;
        $this->memcached_on         = AkMemcache::isServerUp();
        $this->constants            = AkDebug::get_constants();
        $this->langs                = Ak::langs();
        $this->database_settings    = Ak::getSettings('database', false);
        
        $this->server_user          = '';//trim(WIN ? `ECHO %USERNAME%` : `whoami`);

        $this->local_ips = AkConfig::getOption('local_ips', array('localhost','127.0.0.1','::1'));
        //var_dump($this->local_ips);

        $paths = array(
        APP_DIR.DS.'locales',
        );
        $this->invalid_permissions = array();
        //~ echo('OK');
        /*foreach($paths as $path){
            if(is_dir($path) && !@file_put_contents($path.DS.'__test_file')){
                $this->invalid_permissions[] = $path;
            }else{
                @unlink($path.DS.'__test_file');
            }
        }*/

    }

    public function web_terminal(){
        $this->user                 = trim(WIN ? `ECHO %USERNAME%` : `whoami`);
        if(defined('ENABLE_TERMINAL_ON_DEV') && ENABLE_TERMINAL_ON_DEV && !IN_SAE){
            $this->enabled = true;
            $cwd = empty($_SESSION['last_working_directory']) ? BASE_DIR : $_SESSION['last_working_directory'];
            if (!empty($this->params['cmd'])){
                $result = `cd $cwd;{$this->params['cmd']};echo "----rails-cmd----";pwd;`;
                list($response, $last_dir) = explode('----rails-cmd----', $result);
                $_SESSION['last_working_directory'] = trim($last_dir);
                if($response){
                    $this->renderText(AkTextHelper::html_escape($response));
                }else{
                    $this->renderText(AkTextHelper::html_escape($this->t('Error or empty response while running: %command', array('%command' => $this->params['cmd']))));
                }
            }
        }else{
            if (!empty($this->params['cmd'])){
                $this->renderText($this->t('Terminal disabled.'. IN_SAE?"IN_SAE=true":"please define ENABLE_TERMINAL_ON_DEV"));
            }
        }
    }
    
    public function docs () {
    }
    
    public function guide () {
        $this->tab = 'docs';
        $this->docs_helper->docs_path = 'guides';
        $this->title = AkInflector::titleize(@$this->params['id']).', Rails guides';
        $this->guide = $this->docs_helper->get_doc_contents(
            empty($this->params['id']) ? 'getting_started' : $this->params['id']);
    }
}