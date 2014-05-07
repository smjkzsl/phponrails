<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkMarkdownToHtml
{
    public function convert() {
        return $this->source = preg_replace("/([ \n\t]+)/",' ', AkTextHelper::markdown($this->source));
    }
}

