<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkMysqlDbAdapter extends AkDbAdapter
{
    /**
     * @param array $database_settings
     * @return string
     */
    static function constructDsn($database_settings) {
        $dsn  = 'mysqlt://';
        $dsn .= $database_settings['user'].':'.$database_settings['password'];
        $dsn .= !empty($database_settings['host']) ? '@'.$database_settings['host'] : '@localhost';
        $dsn .= !empty($database_settings['port']) ? ':'.$database_settings['port'] : '';
        $dsn .= '/'.$database_settings['database_name'];
        if (empty($database_settings['options']) && !empty($database_settings['socket'])) $database_settings['options'] = 'socket='.urlencode($database_settings['socket']);
        $dsn .= !empty($database_settings['options']) ? '?'.$database_settings['options'] : '';
        return $dsn;
    }

    public function type() {
        return 'mysql';
    }

    public function addLimitAndOffset(&$sql,$options) {
        if (isset($options['limit']) && $limit = $options['limit']){
            if (isset($options['offset']) && $offset = $options['offset'])
            $sql .= " LIMIT ".intval($offset).", ".intval($limit);
            else
            $sql .= " LIMIT ".intval($limit);
        }
        return $sql;
    }

    /* SCHEMA */

    public function renameColumn($table_name,$column_name,$new_name) {
        $column_details = $this->selectOne("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
        if (!$column_details) {
            trigger_error(Ak::t("No such column '%column' in %table_name",array('%column'=>$column_name,'%table_name'=>$table_name)), E_USER_ERROR);
            return false;
        }
        $column_type_definition = $column_details['Type'];
        if ($column_details['Null']!=='YES') $column_type_definition .= ' not null';
        if (!empty($column_details['Default'])) $column_type_definition .= " default '".$column_details['Default']."'";
        return $this->execute("ALTER TABLE $table_name CHANGE COLUMN $column_name $new_name $column_type_definition");
    }

    /* QUOTING */

    public function quote_string($value) {
        return "'".mysql_real_escape_string($value, $this->connection->_connectionID)."'";
    }

    public function connect($die_on_error=true) {
        parent::connect($die_on_error);
        if(defined('SET_UTF8_ON_MYSQL_CONNECT') && SET_UTF8_ON_MYSQL_CONNECT){
            if(isset($this->connection->_connectionID)){
                if(function_exists('mysql_set_charset')){
                    mysql_set_charset('utf8', $this->connection->_connectionID);
                }else{
                    mysql_query('SET CHARACTER SET "utf8"', $this->connection->_connectionID);
                }
            }
        }
    }
}

