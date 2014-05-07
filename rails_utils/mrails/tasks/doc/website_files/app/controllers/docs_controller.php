<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class DocsController extends ApplicationController
{
    private $_authorized_users = array('rails' => 'docs');
    
    public function __construct(){
        if(!AkRequest::isLocal()){
            $this->beforeFilter(array('authenticate' => array('except' => array('index'))));
        }
    }
    
    public function authenticate() {
        return $this->authenticateOrRequestWithHttpBasic('Docs', $this->_authorized_users);
    }
    
    public function index () {
        $this->redirectToAction('guide');
    }

    public function guide () {
        $this->layout = AkConfig::getDir('views').DS.'layouts'.DS.'docs'.DS.'guide.tpl';
        $this->docs_helper->docs_path = ''.DS.'guides';
        //~ $this->docs_helper->docs_path = 'rails'.DS.'guides';
        $this->guide = $this->docs_helper->get_doc_contents(
            empty($this->params['id']) ? 'getting_started' : $this->params['id']);
    }
}

