<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details


class AkModelUtilities extends AkModelExtenssion
{


    /**
     * Reads Xml in the following format:
     *
     *
     * <?xml version="1.0" encoding="UTF-8"?>
     * <person>
     *    <id>1</id>
     *    <first-name>Hansi</first-name>
     *    <last-name>Müller</last-name>
     *    <email>hans@mueller.com</email>
     *    <created-at type="datetime">2008-01-01 13:01:23</created-at>
     * </person>
     *
     * and returns an AkBaseModel Object
     *
     * @param string $xml
     * @return AkBaseModel
     */
    public function fromXml($xml) {
        $array = Ak::convert('xml','array', $xml);
        $array = $this->_fromXmlCleanup($array);
        return $this->_fromArray($array);
    }


    /**
     * Generate a json representation of the model record.
     *
     * parameters:
     *
     * @param array $options
     *
     *              option parameters:
     *             array(
     *              'collection' => array($Person1,$Person), // array of ActiveRecords
     *              'include' => array('association1','association2'), // include the associations when exporting
     *              'exclude' => array('id','name'), // exclude the attribtues
     *              'only' => array('email','last_name') // only export these attributes
     *              )
     * @return string in Json Format
     */
    public function toJson($options = array()) {
        if (is_array($options) && isset($options[0])) {
            $options = array('collection'=>$options);
        }

        if (isset($options['collection']) && (is_array($options['collection']) || ($options['collection'] instanceof ArrayAccess)) && $options['collection'][0]->_modelName == $this->_Model->getModelName()) {
            $json = '';

            $collection = $options['collection'];
            unset($options['collection']);
            $jsonVals = array();
            foreach ($collection as $element) {
                $jsonVals[]= $element->toJson($options);
            }
            $json = '['.implode(',',$jsonVals).']';
            return $json;
        }
        /**
         * see if we need to include associations
         */
        $associatedIds = array();
        if (isset($options['include']) && !empty($options['include'])) {
            $options['include'] = is_array($options['include'])?$options['include']:preg_split('/,\s*/',$options['include']);
            foreach ($this->_Model->getAssociations() as $key => $obj) {
                if (in_array($key,$options['include'])) {
                    $associatedIds[implode('',$obj->getAssociationIds() ). '_id'] = array('name'=>$key,'type'=>$obj->getType());
                }
            }
        }
        if (isset($options['only'])) {
            $options['only'] = is_array($options['only'])?$options['only']:preg_split('/,\s*/',$options['only']);
        }
        if (isset($options['except'])) {
            $options['except'] = is_array($options['except'])?$options['except']:preg_split('/,\s*/',$options['except']);
        }
        foreach ($this->_Model->getColumns() as $key => $def) {

            if (isset($options['except']) && in_array($key, $options['except'])) {
                continue;
            } else if (isset($options['only']) && !in_array($key, $options['only'])) {
                continue;
            } else {
                $val = $this->_Model->$key;
                $type = $this->_Model->getColumnType($key);
                if (($type == 'serial' || $type=='integer') && $val!==null) $val = intval($val);
                if ($type == 'float' && $val!==null) $val = floatval($val);
                if ($type == 'boolean') $val = $val?1:0;
                if ($type == 'datetime' && !empty($val)) {
                    // UTC (Coordinated Universal Time) http://www.w3.org/TR/NOTE-datetime
                    $val = gmdate('Y-m-d\TH:i:s\Z', Ak::getTimestamp($val));
                }

                $data[$key] = $val;
            }
        }
        if (isset($options['include'])) {
            foreach($this->_Model->getAssociations() as $key=>$val) {
                if ((in_array($key,$options['include']) || in_array($val,$options['include']))) {
                    $this->_Model->$key->load();
                    $associationElement = $key;
                    $associationElement = $this->_convertColumnToXmlElement($associationElement);
                    if (is_array($this->_Model->$key)) {
                        $data[$associationElement] = array();
                        foreach ($this->_Model->$key as $el) {
                            if ($el instanceof AkBaseModel) {
                                $attributes = $el->getAttributes();
                                foreach($attributes as $ak=>$av) {
                                    $type = $el->getColumnType($ak);
                                    if (($type == 'serial' || $type=='integer') && $av!==null) $av = intval($av);
                                    if ($type == 'float' && $av!==null) $av = floatval($av);
                                    if ($type == 'boolean') $av = $av?1:0;
                                    if ($type == 'datetime' && !empty($av)) {
                                       // UTC (Coordinated Universal Time) http://www.w3.org/TR/NOTE-datetime
                                       $av = gmdate('Y-m-d\TH:i:s\Z', Ak::getTimestamp($av));
                                    }
                                    $attributes[$ak]=$av;
                                }
                                $data[$associationElement][] = $attributes;
                            }
                        }
                    } else {
                        $el = $this->_Model->$key->load();
                        if ($el instanceof AkBaseModel) {
                            $attributes = $el->getAttributes();
                            foreach($attributes as $ak=>$av) {
                                $type = $el->getColumnType($ak);
                                if (($type == 'serial' || $type=='integer') && $av!==null) $av = intval($av);
                                if ($type == 'float' && $av!==null) $av = floatval($av);
                                if ($type == 'boolean') $av = $av?1:0;
                                if ($type == 'datetime' && !empty($av)) {
                                    // UTC (Coordinated Universal Time) http://www.w3.org/TR/NOTE-datetime
                                    $av = gmdate('Y-m-d\TH:i:s\Z', Ak::getTimestamp($av));
                                }
                                $attributes[$ak]=$av;
                            }
                            $data[$associationElement] = $attributes;
                        }
                    }
                }
            }
        }
        return Ak::toJson($data);
    }



    /**
     * Reads Json string in the following format:
     *
     * {"id":1,"first_name":"Hansi","last_name":"M\u00fcller",
     *  "email":"hans@mueller.com","created_at":"2008-01-01 13:01:23"}
     *
     * and returns an AkBaseModel Object
     *
     * @param string $json
     * @return AkBaseModel
     */
    public function fromJson($json) {
        $json = Ak::fromJson($json);
        $array = Ak::convert('Object','Array',$json);
        return $this->_fromArray($array);
    }

    /**
     * Generate a xml representation of the model record.
     *
     * Example result:
     *
     * <?xml version="1.0" encoding="UTF-8"?>
     * <person>
     *    <id>1</id>
     *    <first-name>Hansi</first-name>
     *    <last-name>Müller</last-name>
     *    <email>hans@mueller.com</email>
     *    <created-at type="datetime">2008-01-01 13:01:23</created-at>
     * </person>
     *
     * parameters:
     *
     * @param array $options
     *
     *              option parameters:
     *             array(
     *              'collection' => array($Person1,$Person), // array of ActiveRecords
     *              'include' => array('association1','association2'), // include the associations when exporting
     *              'exclude' => array('id','name'), // exclude the attribtues
     *              'only' => array('email','last_name') // only export these attributes
     *              )
     * @return string in Xml Format
     */
    public function toXml($options = array()) {
        $options['padding'] = empty($options['padding']) ? 0 : $options['padding']+1;
        $current_padding = str_repeat(' ', $options['padding']);
        if (is_array($options) && isset($options[0]) && ($options[0] instanceof ArrayAccess)) {
            $options = array('collection'=>$options);
        }
        if (isset($options['collection']) && (is_array($options['collection']) || ($options['collection'] instanceof ArrayAccess)) && $options['collection'][0]->_modelName == $this->_Model->getModelName()) {
            $root = AkInflector::underscore(AkInflector::pluralize($this->_Model->getModelName()));
            $root = $this->_convertColumnToXmlElement($root);
            $xml = '';
            if (!(isset($options['skip_instruct']) && $options['skip_instruct'] == true)) {
                $xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            }
            $xml .= $current_padding.'<'. $root . " type=\"array\">\n";
            $collection = $options['collection'];
            unset($options['collection']);
            $options['skip_instruct'] = true;
            $options['padding']++;
            foreach ($collection as $element) {
                $xml .= $element->toXml($options);
            }
            $xml .= $current_padding.'</' . $root .">\n";
            return $xml;
        }
        /**
         * see if we need to include associations
         */
        $associatedIds = array();
        if (isset($options['include']) && !empty($options['include'])) {
            $options['include'] = is_array($options['include'])?$options['include']:preg_split('/,\s*/',$options['include']);
            foreach ($this->_Model->getAssociations() as $key => $obj) {
                if (in_array($key,$options['include'])) {
                    if ($obj->getType()!='hasAndBelongsToMany') {
                        $associatedIds[implode('',$obj->getAssociationIds()) . '_id'] = array('name'=>$key,'type'=>$obj->getType());
                    } else {
                        $associatedIds[$key] = array('name'=>$key,'type'=>$obj->getType());
                    }
                }
            }
        }
        if (isset($options['only'])) {
            $options['only'] = is_array($options['only'])?$options['only']:preg_split('/,\s*/',$options['only']);
        }
        if (isset($options['except'])) {
            $options['except'] = is_array($options['except'])?$options['except']:preg_split('/,\s*/',$options['except']);
        }
        $xml = '';
        if (!(isset($options['skip_instruct']) && $options['skip_instruct'] == true)) {
            $xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        }
        $root = $this->_convertColumnToXmlElement(AkInflector::underscore($this->_Model->getModelName()));
        
        $xml .= $current_padding.'<' . $root . ">\n";
        foreach ($this->_Model->getColumns() as $key => $def) {

            if (isset($options['except']) && in_array($key, $options['except'])) {
                continue;
            } else if (isset($options['only']) && !in_array($key, $options['only'])) {
                continue;
            } else {
                $columnType = $def['type'];
                $elementName = $this->_convertColumnToXmlElement($key);
                $xml .= $current_padding.'  <' . $elementName;
                $val = $this->_Model->$key;
                if (!in_array($columnType,array('string','text','serial'))) {
                    $xml .= ' type="' . $columnType . '"';
                    if ($columnType=='boolean') $val = $val?1:0;
                }elseif ($columnType == 'serial' && is_numeric($val)){
                    $xml .= ' type="integer"';
                }
                if (!empty($val) && in_array($columnType,array('datetime'))) {
                    // UTC (Coordinated Universal Time) http://www.w3.org/TR/NOTE-datetime
                    $val = gmdate('Y-m-d\TH:i:s\Z', Ak::getTimestamp($val));
                }
                if(is_null($val)){
                    $xml .= ' nil="true"';
                }
                
                $xml .= '>' . Ak::utf8($val) . '</' . $elementName . ">\n";
            }
        }
        if (isset($options['include'])) {
            foreach($this->_Model->getAssociations() as $key=>$val) {
                if ((in_array($key,$options['include']) || in_array($val,$options['include']))) {
                    if (is_array($this->_Model->$key)) {

                        $associationElement = $key;
                        $associationElement = AkInflector::underscore(AkInflector::pluralize($associationElement));
                        $associationElement = $this->_convertColumnToXmlElement($associationElement);
                        $xml .= $current_padding.'<'.$associationElement." type=\"array\">\n";
                        $options['padding']++;
                        foreach ($this->_Model->$key as $el) {
                            if ($el instanceof AkBaseModel) {
                                $xml .= $el->toXml(array('skip_instruct'=>true));
                            }
                        }
                        $xml .= $current_padding.'</' . $associationElement .">\n";
                    } else {
                        $el = $this->_Model->$key->load();
                        if ($el instanceof AkBaseModel) {
                            $xml.=$el->toXml(array('skip_instruct'=>true));
                        }
                    }
                }
            }
        }
        $xml .= $current_padding.'</' . $root . ">\n";
        return $xml;
    }
    
    /**
     * Generate a YAML representation of the model record.
     *
     * examples:
     * User::toYaml($users->find('all'));
     * $Bermi->toYaml();
     *
     * @param array of ActiveRecords[optional] $data
     */
    public function toYaml($data = null) {
        return Ak::convert('active_record', 'yaml', empty($data) ? $this->_Model : $data);
    }

    private function _convertColumnToXmlElement($col) {
        return str_replace('_','-',$col);
    }

    private function _convertColumnFromXmlElement($col) {
        return str_replace('-','_',$col);
    }

    private function _parseXmlAttributes($attributes) {
        $new = array();
        foreach($attributes as $key=>$value)
        {
            $new[$this->_convertColumnFromXmlElement($key)] = $value;
        }
        return $new;
    }

    private function &_generateModelFromArray($modelName,$attributes) {
        if (isset($attributes[0]) && is_array($attributes[0])) {
            $attributes = $attributes[0];
        }
        $record = new $modelName('attributes', $this->_parseXmlAttributes($attributes));
        $record->_newRecord = !empty($attributes['id']);

        $associatedIds = array();
        foreach ($record->getAssociatedIds() as $key) {
            if (isset($attributes[$key]) && is_array($attributes[$key])) {
                $class = $record->$key->_AssociationHandler->getOption($key,'class_name');
                $related = $this->_generateModelFromArray($class,$attributes[$key]);
                $record->$key->build($related->getAttributes(),false);
                $related = $record->$key->load();
                $record->$key = $related;
            }
        }
        return $record;
    }

    private function _fromArray($array) {
        $data  = $array;
        $modelName = $this->_Model->getModelName();
        $values = array();
        if (!isset($data[0])) {
            $data = array($data);
        }
        foreach ($data as $key => $value) {
            if (is_array($value)){
                $values[] = $this->_generateModelFromArray($modelName, $value);
            }
        }
        return count($values)==1?$values[0]:$values;
    }

    private function _fromXmlCleanup($array) {
        $result = array();
        $key = key($array);
        while(is_string($key) && is_array($array[$key]) && count($array[$key])==1) {
            $array = $array[$key][0];
            $key = key($array);
        }
        if (is_string($key) && is_array($array[$key])) {
            $array = $array[$key];
        }
        return $array;
    }

}

