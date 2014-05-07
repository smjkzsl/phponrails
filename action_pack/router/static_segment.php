<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkStaticSegment extends AkSegment
{
    public function isCompulsory() {
        return true;
    }

    public function getRegEx() {
        return preg_quote($this->delimiter,'@').$this->name;
    }

    public function generateUrlFromValue($value, $omit_optional_segments) {
        return $this->delimiter.$this->name;    
    }
}

