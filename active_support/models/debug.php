<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkModelDebug extends AkModelExtenssion
{
    /**
     * Toggles SQL query output
     */
    public function dbug() {
        if(!$this->_Model->isConnected()){
            $this->_Model->establishConnection();
        }
        $this->_Model->getAdapter()->connection->debug = $this->_Model->getAdapter()->connection->debug ? false : true;
        $this->_Model->db_debug = $this->_Model->getAdapter()->connection->debug;
    }

    /**
     * Displays a tring representation of the model for debugging.
     */
    public function toString($print = false) {
        $result = '';
        if(!CLI || (ENVIRONMENT == 'testing' && !CLI)){
            $result = "<h2>Details for ".AkInflector::humanize(AkInflector::underscore($this->_Model->getModelName()))." with ".$this->_Model->getPrimaryKey()." ".$this->_Model->getId()."</h2>\n<dl>\n";
            foreach ($this->_Model->getColumnNames() as $column=>$caption){
                $result .= "<dt>$caption</dt>\n<dd>".$this->_Model->getAttribute($column)."</dd>\n";
            }
            $result .= "</dl>\n<hr />";
            if($print){
                echo $result;
            }
        }elseif(DEV_MODE){
            $result =   "\n".
            str_replace("\n"," ",var_export($this->_Model->getAttributes(),true));
            $result .= "\n";
            echo $result;
            return '';
        }elseif (CLI){
            $result = "\n-------\n Details for ".AkInflector::humanize(AkInflector::underscore($this->_Model->getModelName()))." with ".$this->_Model->getPrimaryKey()." ".$this->_Model->getId()." ==\n\n/==\n";
            foreach ($this->_Model->getColumnNames() as $column=>$caption){
                $result .= "\t * $caption: ".$this->_Model->getAttribute($column)."\n";
            }
            $result .= "\n\n-------\n";
            if($print){
                echo $result;
            }
        }
        return $result;
    }

    public function dbugging($trace_this_on_debug_mode = null) {
        if(!empty($this->_Model->getAdapter()->debug) && !empty($trace_this_on_debug_mode)){
            $message = !is_scalar($trace_this_on_debug_mode) ? var_export($trace_this_on_debug_mode, true) : (string)$trace_this_on_debug_mode;
            AkDebug::trace($message);
        }
        return !empty($this->_Model->getAdapter()->debug);
    }



    public function debug ($data = 'active_record_class', $_functions=0) {
        if(!DEBUG && !DEV_MODE){
            return;
        }

        $data = $data == 'active_record_class' ?  clone($this->_Model) : $data;

        if($_functions!=0) {
            $sf=1;
        } else {
            $sf=0 ;
        }

        if (isset ($data)) {
            if (is_array($data) || is_object($data)) {

                if (count ($data)) {
                    echo CLI ? "/--\n" : "<ol>\n";
                    while (list ($key,$value) = each ($data)) {
                        if($key{0} == '_'){
                            continue;
                        }
                        $type=gettype($value);
                        if ($type=="array") {
                            CLI ? printf ("\t* (%s) %s:\n",$type, $key) :
                            printf ("<li>(%s) <b>%s</b>:\n",$type, $key);
                            ob_start();
                            AkDebug::debug ($value,$sf);
                            $lines = explode("\n",ob_get_clean()."\n");
                            foreach ($lines as $line){
                                echo "\t".$line."\n";
                            }
                        }elseif($type == "object"){
                            if(method_exists($value,'hasColumn') && $value->hasColumn($key)){
                                $value->toString(true);
                                CLI ? printf ("\t* (%s) %s:\n",$type, $key) :
                                printf ("<li>(%s) <b>%s</b>:\n",$type, $key);
                                ob_start();
                                AkDebug::debug ($value,$sf);
                                $lines = explode("\n",ob_get_clean()."\n");
                                foreach ($lines as $line){
                                    echo "\t".$line."\n";
                                }
                            }
                        }elseif (stristr($type, "function")) {
                            if ($sf) {
                                CLI ? printf ("\t* (%s) %s:\n",$type, $key, $value) :
                                printf ("<li>(%s) <b>%s</b> </li>\n",$type, $key, $value);
                            }
                        } else {
                            if (!$value) {
                                $value = "(none)";
                            }
                            CLI ? printf ("\t* (%s) %s = %s\n",$type, $key, $value) :
                            printf ("<li>(%s) <b>%s</b> = %s</li>\n",$type, $key, $value);
                        }
                    }
                    echo CLI ? "\n--/\n" : "</ol>fin.\n";
                } else {
                    echo "(empty)";
                }
            }
        }
    }
}
