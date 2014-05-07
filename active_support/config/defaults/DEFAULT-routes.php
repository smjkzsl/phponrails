<?php

// Routes define how different parts of your application are accessed via URLs
// if you're new to Rails the default routes will work for you


/**
 * This route will enable the Rails development panel at /dev_panel
 * when browsing from localhost
 * /
$Map->connect('/dev_panel/:controller/:action/:id', array(
              'controller' => 'rails_dashboard', 
              'action' => 'index', 
              'module' => 'rails_panel',
              'rebase' => RAILS_UTILS_DIR.DS.'rails_panel'
            ));
/* */

$Map->connect('/:controller/:action/:id', array('controller' => 'page', 'action' => 'index'));
$Map->connect('/', array('controller' => 'page', 'action' => 'index'));

