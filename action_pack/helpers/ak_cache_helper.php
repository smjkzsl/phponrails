<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
 * Cache Helpers lets you cache fragments of templates
*
* == Caching a block into a fragment
*
*   <b>Hello {name}</b>
*   <?php if (!$cache_helper->begin()) { ?>
*     All the topics in the system:
*     <?= $controller->renderPartial("topic", $Topic->findAll()); ?>
*   <?= $cache_helper->end();} ?>
*  
*
*
*   Normal view text
*/
class AkCacheHelper extends AkBaseHelper 
{
    
    public function begin($key = array(), $options = array()) {
        return $this->_controller->cacheTplFragmentStart($key, $options);
    }

    public function end($key = array(), $options = array()) {
        return $this->_controller->cacheTplFragmentEnd($key, $options);
    }
}
