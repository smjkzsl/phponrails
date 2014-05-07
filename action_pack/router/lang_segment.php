<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkLangSegment extends AkVariableSegment 
{
    public function __construct($name,$delimiter,$default=null,$requirement=null) {
        if (!$requirement){
            $requirement = '('.join('|',$this->availableLocales()).')';  
        }
        parent::__construct($name,$delimiter,$default,$requirement);
    }

    public function isOmitable() {
        return true;
    }

    private function availableLocales() {
        return Ak::langs();
    }

}

