<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkHtmlToSmartypants
{
    public function convert() {
        require_once(CONTRIB_DIR.DS.'TextParsers'.DS.'smartypants.php');
        $Smartypants = new SmartyPantsTypographer_Parser();
        return $Smartypants->transform($this->source);
    }
}

