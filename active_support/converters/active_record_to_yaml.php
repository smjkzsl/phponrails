<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkActiveRecordToYaml
{
    public function convert() {
        $attributes = array();
        if($this->source instanceof ArrayAccess){
            foreach ($this->source as $Model){
                if($Model instanceof AkBaseModel){
                    $attributes[$Model->getId()] = $Model->getAttributes();
                }
            }
        } elseif ($this->source instanceof AkBaseModel){
            $attributes[$this->source->getId()] = $this->source->getAttributes();
        }
        require_once(CONTRIB_DIR.DS.'TextParsers'.DS.'spyc.php');
        return Spyc::YAMLDump($attributes);
    }
}

