<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class RailsPanel_VirtualAssetsController extends RailsPanelController {

    public $layout = false;
        
    public function stylesheets(){
        $this->renderAction(@$this->params['id']);
    }
    public function javascripts(){
        $this->renderAction(@$this->params['id']);
    }
    public function images(){
        $file_path = AkConfig::getDir('views').DS.'rails_panel'.DS.'virtual_assets'.DS.'images'.DS.str_replace('.', '',@$this->params['id']).'.'.@$this->params['format'];
        
        $this->sendFile($file_path, array('disposition' => 'inline'));
    }
    public function guide_images(){
        $file_path = AkConfig::getDir('views').DS.'rails_panel'.DS.'virtual_assets'.DS.'guide_images'.DS.str_replace('.', '',@$this->params['id']).'.'.@$this->params['format'];
        $this->sendFile($file_path, array('disposition' => 'inline'));
    }
}
