<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkReflectionFunction extends AkReflection
{
    protected
    $_definition,
    $_docBlock;

    public
    $methods = array(),
    $properties = array();

    public function __construct($method_definition) {
        if (is_array($method_definition)) {
            if (@$method_definition['type'] == 'function') {
                $this->_definition = $method_definition;
            } else {
                return;
            }
        } else if (is_string($method_definition)) {
            $this->parse($method_definition);
            foreach ($this->definitions as $def) {
                if ($def['type'] == 'function') {
                    $this->_definition = $def;
                    break;
                }
            }
            $this->definitions = array();
            $this->tokens = array();
        } else {
            return;
        }
        $this->_docBlock = new AkReflectionDocBlock($this->_definition['docBlock']);
        $this->parse($this->_definition['code']);
        $this->_parseDefinitions();
    }

    public function getDefaultOptions() {
        return isset($this->_definition['default_options']) ? $this->_definition['default_options'] : false;
    }

    public function getAvailableOptions() {
        return isset($this->_definition['available_options']) ? $this->_definition['available_options'] : false;
    }

    public function getName() {
        return isset($this->_definition['name']) ? $this->_definition['name'] : false;
    }

    public function getDefinition() {
        return $this->_definition;
    }

    public function setTag($tag,$value) {
        if (!is_object($this->_docBlock)) {
            $this->_docBlock = new AkReflectionDocBlock('');
        }
        $this->_docBlock->setTag($tag,$value);
    }

    public function getTag($tag) {
        return $this->_docBlock->getTag($tag);
    }

    public function getParams() {
        return isset($this->_definition['params']) ? $this->_definition['params'] : false;
    }

    public function getCode() {
        return $this->_definition['code'];
    }

    public function toString($indent=0, $methodName = null, $options = array()) {
        $docBlock = $this->_docBlock;
        if ($docBlock->changed) {
            $string = $this->_definition['toString'];
            $orgDocBlock = trim($docBlock->original);
            if (!empty($orgDocBlock)) {
                $string = str_replace($orgDocBlock, $docBlock->toString(), $string);
            } else {
                $string = $docBlock->toString()."\n".$string;
            }
        } else {
            $string=isset($this->_definition['toString']) ? $this->_definition['toString'] : null;
        }
        if ($indent>0) {
            $lines = explode("\n", $string);
            foreach ($lines as $idx=>$line) {
                $lines[$idx] = str_repeat(' ',$indent).$line;
            }
            $string = implode("\n",$lines);
        }
        if ($methodName!=null) {
            $string = preg_replace('/function(.*?)('.$this->getName().')(.*?)\(/','function\\1'.$methodName.'\\3(',$string);
        }
        if(isset($options['visibility'])){
            $string = preg_replace('/(private|public|protected) function(.*?)('.$this->getName().')(.*?)\(/', $options['visibility'].' function\\2'.$this->getName().'\\4(',$string);
        }

        return $string;
    }

    public function returnByReference() {
        return isset($this->_definition['returnByReference']) ? $this->_definition['returnByReference'] : false;
    }

    public function &getDocBlock() {
        return $this->_docBlock;
    }

    public function _parseDefinitions() {
        foreach($this->definitions as $definition) {
            if(isset($definition['type'])){
                switch ($definition['type']) {
                    case 'function':
                        $this->methods[] = new AkReflectionMethod($definition);
                        break;
                }
            }
        }
    }
}

