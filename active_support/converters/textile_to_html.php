<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkTextileToHtml
{
    public function convert() {
        require_once(ACTION_PACK_DIR.DS.'helpers'.DS.'text_helper.php');
        return AkTextHelper::textilize($this->source);
    }
}

