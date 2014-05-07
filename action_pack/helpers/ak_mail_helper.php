<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkMailHelper
{
    public function setController(&$controller) {
        $this->_controller = $controller;
    }

   /**
   * Uses AkTextHelper::format to take the text and format it, indented two spaces for
   * each line, and wrapped at 72 columns.
   */
    public function block_format($text) {
        $formatted = '';
        $paragraphs = preg_split("/(\n|\r){2,}/", $text);
        foreach ((array)$paragraphs as $paragraph){
            $formatted .= AkTextHelper::format($paragraph, array('columns' => 72, 'first_indent' => 2, 'body_indent' => 2));
        }
        // Make list points stand on their own line
        return preg_replace("/[ ]*([*]+) ([^*]*)/"," $1 $2\n", preg_replace("/[ ]*([#]+) ([^#]*)/"," $1 $2\n",$formatted));
    }
}
