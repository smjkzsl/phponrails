<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkHtmlToMarkdown
{
    public function convert() {
        require_once(CONTRIB_DIR.DS.'TextParsers'.DS.'html2text.php');
        $Converter = new html2text(true, 0, false);
        return $Converter->load_string($this->source);
    }
}

