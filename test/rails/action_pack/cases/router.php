<?php

require_once(dirname(__FILE__).'/../config.php');

class RouterUnitTest extends AkRouterUnitTest
{

}

$_router_files = glob(dirname(__FILE__).DS.'router'.DS.'*.php');
$_included_files = get_included_files();
if(count($_included_files) == count(array_diff($_included_files, $_router_files))){
    foreach ($_router_files as $file){
        include $file;
    }
}
/*
 $Map->connect('/admin/:controller/:action/:id', array('controller' => 'dashboard', 'action' => 'index', 'module' => 'admin'));

// Routes define how different parts of your application are accessed via URLs
// if you're new to Rails the default routes will work for you

$Map->root(array('controller' => 'home'));


/**
 * This route will enable the Rails development panel at / on fresh installs
 * when browsing from localhost.
 * 
 * You need to comment this route or point it to a different base in order to accept
 * Requests in your application.
 *//*
 $Map->connect('/dev/:controller/:action/:id', array(
 //$Map->connect('/:controller/:action/:id', array(
              'controller' => 'rails_dashboard', 
              'action' => 'index', 
              'module' => 'rails_panel'
            ), array( 'rebase' => AKELOS_UTILS_DIR.DS.'rails_panel'));
/* */
/*
//$Map->connect('/posts/:controller/:action/:id');
$Map->connect(':controller/:action/:id');
$Map->connect(':controller/:action/:id.:format');*/
unset($_router_files);
unset($_included_files);
