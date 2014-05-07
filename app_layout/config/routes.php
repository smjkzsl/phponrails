<?php

// Routes define how different parts of your application are accessed via URLs
// if you're new to Rails the default routes will work for you

$Map->root(array('controller' => 'home'));
 

/**
 * This route will enable the Rails development panel at / on fresh installs
 * when browsing from localhost.
 * 
 * You need to comment this route or point it to a different base in order to accept
 * Requests in your application.
 */
 $Map->connect('/dev_panel/:controller/:action/:id', array(
 //$Map->connect('/:controller/:action/:id', array(
              'controller' => 'rails_dashboard', 
              'action' => 'index', 
              'module' => 'rails_panel'
            ), array('module' => 'rails_panel',
     'rebase' => RAILS_UTILS_DIR.DS.'rails_panel'));
/* */


$Map->connect(':controller/:action/:id');
$Map->connect(':controller/:action/:id.:format');