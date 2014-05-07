<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkYamlToArray
{
    public function convert() {
        include_once CONTRIB_DIR.DS.'TextParsers'.DS.'spyc.php';
        return Spyc::YAMLLoad($this->source);
    }
}

