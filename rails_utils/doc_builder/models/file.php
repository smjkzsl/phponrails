<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class File extends AkActiveRecord
{
    public $has_many = array('methods');
    public $belongs_to = array('component', 'category');

    public function validate()
    {
        $this->validatesUniquenessOf('path');
    }
}

