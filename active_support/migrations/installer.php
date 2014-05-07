<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
 * == Column Types ==
 *
 * Rails natively supports the following column data types.
 *
 *      integer|int, float, decimal,
 *      string, text,
 *      datetime|timestamp, date,
 *      binary,
 *      boolean
 *
 * Caution: Because boolean is virtual tinyint on mysql, you can't use tinyint for other things!
 *
 *
 * == Default settings for columns ==
 *
 * AkInstaller suggests some default values for the column-details.
 *
 * So
 * <code>
 *     $this->createTable('Post','title,body,created_at,is_draft');
 * </code>
 *
 * will actually create something like this:
 *
 *     title => string(255), body => text, created_at => datetime, is_draft => boolean not null default 0 index
 *
 *
 * column_name                    | default setting
 * -------------------------------+--------------------------------------------
 * id                             | integer not null auto_increment primary_key
 * *_id,*_by                      | integer index
 * description,content,body       | text
 * position                       | integer index
 * *_count                        | integer default 0
 * lock_version                   | integer default 1
 * *_at                           | datetime
 * *_on                           | date
 * is_*,has_*,do_*,does_*,are_*   | boolean not null default 0 index
 * *somename                      | multilingual column => en_somename, es_somename
 * default                        | string
 *
 */
class AkInstaller
{
    public
    // Public paths, so they can be modified for testing purposes
    $app_plugins_dir,
    $app_app_dir,
    $app_base_dir,
    $app_installers_dir,
    $app_tmp_dir,
    $app_vendor_dir,

    $data_dictionary,
    $available_tables           = array(),
    $vervose                    = VERBOSE_INSTALLER,
    $module,
    $warn_if_same_version       = true,
    $use_transactions           = true,
    $skip_db_sql                = false;

    protected
    $_inited                    = false;

    public function __construct($db_connection = null) {
        $this->app_plugins_dir            = AkConfig::getDir('app_plugins');
        $this->app_app_dir                = AkConfig::getDir('app');
        $this->app_base_dir               = AkConfig::getDir('base');
        $this->app_installers_dir         = AkConfig::getDir('app_installers');
        $this->app_tmp_dir                = AkConfig::getDir('tmp');
        $this->app_vendor_dir             = AkConfig::getDir('app_vendor');

        if(!empty($db_connection)){
            $this->init($db_connection);
        }
    }

    public function init($db_connection = null, $reinit = false) {
        if(!$this->_inited || $reinit){

            // Install scripts might use more RAM than normal requests.
            @ini_set('memory_limit', -1);

            if(empty($db_connection)){
                $this->db = AkDbAdapter::getInstance();
            }else {
                $this->db = $db_connection;
            }

            $this->ensureRailsMigrationsModelIsAvailable();

            AkDbSchemaCache::clearAll();

            $this->data_dictionary = $this->db->getDictionary();
            $this->available_tables = $this->getAvailableTables();
            $this->_inited = true;
        }
    }

    public function __get($name) {
        if($name == 'db'){
            $this->init();
        }elseif($name == 'RailsMigration'){
            $this->ensureRailsMigrationsModelIsAvailable();
        }
        return isset($this->$name) ? $this->$name : null;
    }

    public function usage() {
        echo Ak::t("Description:
    Database migrations is a sort of SCM like subversion, but for database settings.

    The migration command takes the name of an installer located on your
    /app/installers folder and runs one of the following commands:

    - \"install\" + (options version number): Will update to the provided version
    number or to the latest one in no version is given.

    - \"uninstall\" + (options version number): Will downgrade to the provided
    version number or to the lowest one in no version is given.

    Current version number will be sorted at app/installers/installer_name_version.txt.

Example:
    >> migrate framework install

    Will run the default database schema for the framework.
    This generates the tables for handling database driven sessions and cache.

");
    }

    public function install($version = null, $options = array()) {
        $version = (is_null($version)) ? max($this->getAvailableVersions()) : $version;
        return $this->_upgradeOrDowngrade('up',  $version , $options);
    }

    public function up($version = null, $options = array()) {
        return $this->_upgradeOrDowngrade('up', $version, $options);
    }

    public function uninstall($version = null, $options = array()) {
        $version = (is_null($version)) ? 0 : $version;
        return $this->_upgradeOrDowngrade('down', $version, $options);
    }

    public function down($version = null, $options = array()) {
        return $this->_upgradeOrDowngrade('down', $version, $options);
    }

    public function execute($sql) {
        return $this->db->execute($sql);
    }

    public function debug($toggle = null) {
        if(empty($this->skip_db_sql)){
            $this->db->connection->debug = $toggle === null ? !$this->db->connection->debug : $toggle;
        }
    }

    public function dropTable($table_name, $options = array()) {
        AkDbSchemaCache::clear($table_name);
        $result = $this->tableExists($table_name) ? $this->db->execute('DROP TABLE '.$this->db->quoteTableName($table_name)) : 1;
        if($result){
            unset($this->available_tables[array_search($table_name, $this->available_tables)]);
            if(!empty($options['sequence'])){
                $this->dropSequence($table_name);
            }
        }
    }

    public function dropTables() {
        $args = func_get_args();
        if(!empty($args)){
            $num_args = count($args);
            $options = $num_args > 1 && is_array($args[$num_args-1]) ? array_shift($args) : array();
            $tables = count($args) > 1 ? $args : (is_array($args[0]) ? $args[0] : Ak::toArray($args[0]));
            foreach ($tables as $table){
                $this->dropTable($table, $options);
            }
        }
    }

    public function addIndex($table_name, $columns, $index_name = '') {
        $index_name = ($index_name == '') ? 'idx_'.$table_name.'_'.$columns : $index_name;
        $index_options = array();
        if(preg_match('/(UNIQUE|FULLTEXT|HASH)/',$columns,$match)){
            $columns = trim(str_replace($match[1],'',$columns),' ');
            $index_options[] = $match[1];
        }
        return $this->tableExists($table_name) ? $this->data_dictionary->ExecuteSQLArray($this->data_dictionary->CreateIndexSQL($index_name, $table_name, $columns, $index_options)) : false;
    }

    public function removeIndex($table_name, $columns_or_index_name) {
        AkDbSchemaCache::clear($table_name);
        if(!$this->tableExists($table_name)){
            return false;
        }
        $available_indexes = $this->db->getIndexes($table_name);
        $index_name = isset($available_indexes[$columns_or_index_name]) ? $columns_or_index_name : 'idx_'.$table_name.'_'.$columns_or_index_name;
        if(!isset($available_indexes[$index_name])){
            trigger_error(Ak::t('Index %index_name does not exist.', array('%index_name'=>$index_name)), E_USER_NOTICE);
            return false;
        }
        return $this->data_dictionary->ExecuteSQLArray($this->data_dictionary->DropIndexSQL($index_name, $table_name));
    }

    public function dropIndex($table_name, $columns_or_index_name) {
        $this->clearSchemaCacheForTable($table_name);
        return $this->removeIndex($table_name,$columns_or_index_name);
    }

    public function createSequence($table_name) {
        $this->clearSchemaCacheForTable($table_name);
        $result = $this->tableExists('seq_'.$table_name) ? false : $this->db->connection->CreateSequence('seq_'.$table_name);
        $this->available_tables[] = 'seq_'.$table_name;
        return $result;
    }

    public function dropSequence($table_name) {
        $this->clearSchemaCacheForTable($table_name);
        $result = $this->tableExists('seq_'.$table_name) ? $this->db->connection->DropSequence('seq_'.$table_name) : true;
        if($result){
            unset($this->available_tables[array_search('seq_'.$table_name, $this->available_tables)]);

        }
        return $result;
    }

    public function getAvailableTables() {
        return $this->available_tables = $this->db->getAvailableTables(true);
    }

    public function tableExists($table_name) {
        return in_array($table_name, $this->getAvailableTables());
    }

    /**
    * Transaction support for database operations
    *
    * Transactions are enabled automatically for Intaller objects, But you can nest transactions within models.
    * This transactions are nested, and only the othermost will be executed
    *
    *   $UserInstalller->transactionStart();
    *   $UserInstalller->addTable('id, name');
    *
    *    if(!isCompatible()){
    *       $User->transactionFail();
    *    }
    *
    *   $User->transactionComplete();
    */
    public function transactionStart() {
        return ($this->use_transactions ? $this->db->startTransaction() : true);
    }

    public function transactionComplete() {
        return ($this->use_transactions ? $this->db->stopTransaction() : true);
    }

    public function transactionFail() {
        return ($this->use_transactions ? $this->db->failTransaction() : true);
    }

    public function transactionHasFailed() {
        return ($this->use_transactions ? $this->db->hasTransactionFailed() : false);
    }


    /**
     * @deprecated
     */
    static function promptUserVar($message, $options = array()) {
        Ak::deprecateMethod('promptUserVar', 'AkConsole::promptUserVar');
        AkConsole::promptUserVar($message, $options);
    }

    public function installVersion($version, $options = array()) {
        return $this->_runInstallerMethod('up', $version, $options);
    }

    public function uninstallVersion($version, $options = array()) {
        return $this->_runInstallerMethod('down', $version, $options);
    }

    public function modifyTable($table_name, $column_options = null, $table_options = array()) {
        return $this->_createOrModifyTable($table_name, $column_options, $table_options);
    }

    /**
     * Adds a new column to the table called $table_name
     */
    public function addColumn($table_name, $column_details) {
        $this->clearSchemaCacheForTable($table_name);
        $this->timestamps = false;
        $this->id = false;
        $column_details = $this->_getColumnsAsAdodbDataDictionaryString($column_details);
        return $this->data_dictionary->ExecuteSQLArray($this->data_dictionary->AddColumnSQL($table_name, $column_details));
    }

    public function changeColumn($table_name, $column_details) {
        $this->clearSchemaCacheForTable($table_name);
        $this->timestamps = false;
        $this->id = false;
        $column_details = $this->_getColumnsAsAdodbDataDictionaryString($column_details);
        return $this->data_dictionary->ExecuteSQLArray($this->data_dictionary->AlterColumnSQL($table_name, $column_details));
    }

    public function removeColumn($table_name, $column_name) {
        $this->clearSchemaCacheForTable($table_name);
        return $this->data_dictionary->ExecuteSQLArray($this->data_dictionary->DropColumnSQL($table_name, $column_name));
    }

    public function renameColumn($table_name, $old_column_name, $new_column_name) {
        $this->clearSchemaCacheForTable($table_name);
        return $this->db->renameColumn($table_name, $old_column_name, $new_column_name);
    }

    public function createTable($table_name, $column_options = null, $table_options = array()) {
        if($this->tableExists($table_name)){
            trigger_error(Ak::t('Table %table_name already exists on the database', array('%table_name'=>$table_name)), E_USER_NOTICE);
            return false;
        }

        $this->_setAutomaticTimestampColumns($column_options, $table_options);
        $this->_setAutomaticIdColumn($column_options, $table_options);

        return $this->_createOrModifyTable($table_name, $column_options, $table_options);
    }

    public function clearSchemaCacheForTable($table_name) {
        AkDbSchemaCache::clear($table_name);
    }

    protected function _setAutomaticTimestampColumns($column_options, $table_options) {
        $this->timestamps = (!isset($table_options['timestamp']) || (isset($table_options['timestamp']) && $table_options['timestamp'])) &&
        (!strstr($column_options, 'created') && !strstr($column_options, 'updated'));
    }

    protected function _setAutomaticIdColumn($column_options, $table_options) {
        if(preg_match('/^id[, ]/', trim($column_options))){
            $this->id = false;
        }else{
            $this->id = (isset($table_options['id']) && $table_options['id']) || !isset($table_options['id']);
        }
    }

    protected function _upgradeOrDowngrade($action, $version = null, $options = array()) {
        AkDbSchemaCache::clearAll();

        if(in_array('quiet',$options) && DEV_MODE){
            $this->vervose = false;
        }elseif(!empty($this->vervose) && DEV_MODE){
            $this->debug(true);
        }

        $current_version = $this->getInstalledVersion($options);
        $available_versions = $this->getAvailableVersions();

        $action = stristr($action,'down') ? 'down' : 'up';

        if($action == 'up'){
            $newest_version = max($available_versions);
            $version = isset($version) && is_numeric($version) ? $version :
            (isset($version[0]) && is_numeric($version[0]) ? $version[0] : $newest_version);

            $versions = range($current_version+1,$version);

            if($current_version > $version){
                echo Ak::t("You can't upgrade to version %version on the installer %installer_name, when you are currently on version %current_version", array('%version'=>$version,'%current_version'=>$current_version, '%installer_name' => $this->getInstallerName()))."\n";
                return false;
            }
        }else{
            $version = isset($version) && is_numeric($version) ? $version :
            (isset($version[0]) && is_numeric($version[0]) ? $version[0] : 0);

            $versions = range($current_version, empty($version) ? 1 : $version+1);

            if($current_version == 0){
                return true;
            }elseif($current_version < $version){
                echo Ak::t("You can't downgrade to version %version on the installer %installer_name, when you just have installed version %current_version", array('%version'=>$version,'%current_version'=>$current_version, '%installer_name' => $this->getInstallerName()))."\n";
                return false;
            }
        }

        if($this->warn_if_same_version && $current_version == $version && ENVIRONMENT != 'setup'){
            echo Ak::t("Can't go %action to version %version on the installer %installer_name, you're already on version %version", array('%version'=>$version, '%installer_name' => $this->getInstallerName(), '%action' => $action))."\n";
            return false;
        }

        if(CLI && !empty($this->vervose) && (DEV_MODE || TEST_MODE)){
            echo "\n".Ak::t('[%installer_name] '.ucfirst($action).'grading to version %version', array('%version'=>$version, '%installer_name'=>$this->getInstallerName()));
        }

        if(!empty($versions) && is_array($versions)){
            foreach ($versions as $version){
                if(!$this->_runInstallerMethod($action, $version, $options)){
                    return false;
                }
            }
        }else{
            return false;
        }

        return true;
    }

    /**
     * Runs a a dow_1, up_3 method and wraps it into a transaction.
     */
    protected function _runInstallerMethod($method_prefix, $version, $options = array(), $version_number = null) {
        $method_name = $method_prefix.'_'.$version;
        $version_number = empty($version_number) ? ($method_prefix=='down' ? $version-1 : $version) : $version_number;

        if(!method_exists($this, $method_name)){
            if($method_prefix == 'down'){
                $this->setInstalledVersion($version_number, $options);
                return true;
            }else{
                return false;
            }
        }

        $this->transactionStart();

        if($this->$method_name($options) === false){
            $this->log($this->getInstallerName().': returned false');
            $this->transactionFail();
        }
        $success = !$this->transactionHasFailed();
        $this->transactionComplete();
        if($success){
            $this->setInstalledVersion($version_number, $options);
        }
        return $success;
    }

    protected function _createOrModifyTable($table_name, $column_options = null, $table_options = array()) {
        $this->clearSchemaCacheForTable($table_name);

        if(empty($column_options)){
            trigger_error(Ak::t('You must supply details for the table you are creating.'), E_USER_ERROR);
            return false;
        }

        $column_options = is_string($column_options) ? array('columns'=>$column_options) : $column_options;

        $default_column_options = array(
        'sequence_table' => false
        );
        $column_options = array_merge($default_column_options, $column_options);

        $default_table_options = array(
        'mysql' => 'ENGINE=InnoDB DEFAULT CHARSET='.str_replace('-', '', strtolower(CHARSET)),
        );
        $table_options = array_merge($default_table_options, $table_options);

        $column_string = $this->_getColumnsAsAdodbDataDictionaryString($column_options['columns']);

        $create_or_alter_table_sql = $this->data_dictionary->ChangeTableSQL(
        $this->db->quoteTableName($table_name),
        str_replace(array(' UNIQUE', ' INDEX', ' FULLTEXT', ' HASH'), '', $column_string),
        $table_options);

        $result = $this->data_dictionary->ExecuteSQLArray($create_or_alter_table_sql, false);

        if($result){
            $this->available_tables[] = $table_name;
        }else{
            trigger_error(Ak::t("Could not create or alter table %name using the SQL \n--------\n%sql\n--------\n", array('%name'=>$table_name, '%sql'=>$create_or_alter_table_sql[0])), E_USER_ERROR);
        }

        $columns_to_index = $this->_getColumnsToIndex($column_string);

        foreach ($columns_to_index as $column_to_index => $index_type){
            $this->addIndex($table_name, $column_to_index.($index_type != 'INDEX' ? ' '.$index_type : ''));
        }

        if(isset($column_options['index_columns'])){
            $this->addIndex($table_name, $column_options['index_columns']);

        }
        if($column_options['sequence_table'] || $this->_requiresSequenceTable($column_string)){
            $this->createSequence($table_name);
        }

        return $result;
    }

    protected function _getColumnsAsAdodbDataDictionaryString($columns) {
        $columns = $this->_setColumnDefaults($columns);
        $this->_ensureColumnNameCompatibility($columns);

        $equivalences = array(
        '/ ((limit|max|length) ?= ?)([0-9]+)([ \n\r,]+)/'=> ' (\3) ',
        '/([ \n\r,]+)default([ =]+)([^\'^,^\n]+)/i'=> ' DEFAULT \'\3\'',
        '/([ \n\r,]+)(integer|int)([( \n\r,]+)/'=> '\1 I \3',
        '/([ \n\r,]+)float([( \n\r,]+)/'=> '\1 F \2',
        '/([ \n\r,]+)decimal([( \n\r,]+)/'=> '\1 N \2',
        '/([ \n\r,]+)datetime([( \n\r,]+)/'=> '\1 T \2',
        '/([ \n\r,]+)date([( \n\r,]+)/'=> '\1 D \2',
        '/([ \n\r,]+)timestamp([( \n\r,]+)/'=> '\1 T \2',
        '/([ \n\r,]+)time([( \n\r,]+)/'=> '\1 T \2',
        '/([ \n\r,]+)text([( \n\r,]+)/'=> '\1 XL \2',
        '/([ \n\r,]+)string([( \n\r,]+)/'=> '\1 C \2',
        '/([ \n\r,]+)binary([( \n\r,]+)/'=> '\1 B \2',
        '/([ \n\r,]+)boolean([( \n\r,]+)/'=> '\1 L'.($this->db->type()=='mysql'?'(1)':'').' \2',
        '/ NOT( |_)?NULL/i'=> ' NOTNULL',
        '/ AUTO( |_)?INCREMENT/i'=> ' AUTO ',
        '/ +/'=> ' ',
        '/ ([\(,]+)/'=> '\1',
        '/ INDEX| IDX/i'=> ' INDEX ',
        '/ UNIQUE/i'=> ' UNIQUE ',
        '/ HASH/i'=> ' HASH ',
        '/ FULL_?TEXT/i'=> ' FULLTEXT ',
        '/ ((PRIMARY( |_)?)?KEY|pk)/i'=> ' KEY',
        );

        $result = trim(preg_replace(array_keys($equivalences),array_values($equivalences), ' '.$columns.' '), ' ');
        $result = preg_replace('/([A-Za-z_0-9-]+)([^,]+)/', '`$1`$2', $result);
        return $result;
    }

    private function _quoteColumnForDataDictionary($matches){
        // '`$1`$2'    , array($this, '_quoteColumnForDataDictionary')
        return str_replace($matches[1],  $this->db->quoteColumnName(trim($matches[1], '"\'`')), $matches[0]);
    }

    protected function _setColumnDefaults($columns) {
        if(!isset($this->id) || $this->id){
            $single_columns = array('id' => $this->_setColumnDefault('id'));
        }
        $columns = Ak::toArray($columns);
        foreach ((array)$columns as $column){
            $column = trim($column, "\n\t\r, ");
            if(!empty($column)){
                $single_columns[$column] = $this->_setColumnDefault($column);
            }
        }
        if(!empty($this->timestamps) && !isset($single_columns['created_at']) &&  !isset($single_columns['updated_at'])){
            $single_columns['updated_at'] = $this->_setColumnDefault('updated_at');
            $single_columns['created_at'] = $this->_setColumnDefault('created_at');
        }
        return join(",\n", $single_columns);
    }

    protected function _setColumnDefault($column) {
        return $this->_needsDefaultAttributes($column) ? $this->_setDefaultAttributes($column) : $column;
    }

    protected function _needsDefaultAttributes($column) {
        return preg_match('/^(([A-Z0-9_\(\)]+)|(.+ string[^\(.]*)|(\*.*))$/i',$column);
    }

    protected function _setDefaultAttributes($column) {
        $rules = $this->getDefaultColumnAttributesRules();
        foreach ($rules as $regex=>$replacement){
            if(is_string($replacement)){
                $column = preg_replace($regex,$replacement,$column);
            }elseif(preg_match($regex,$column,$match)){
                $column = call_user_func_array($replacement,$match);
            }
        }
        return $column;
    }

    /**
     * Returns a key => value pair of regular expressions that will trigger methods
     * to cast database columns to their respective default values or a replacement expression.
     */
    public function getDefaultColumnAttributesRules() {
        return array(
        '/^\*(.*)$/i' => array($this,'_castToMultilingualColumn'),
        '/^(description|content|body|details)$/i' => '\1 text',
        '/^(lock_version)$/i' => '\1 integer default \'1\'',
        '/^(.+_count)$/i' => '\1 integer default \'0\'',
        '/^(id)$/i' => 'id integer not null auto_increment primary_key',
        '/^(.+)_(id|by)$/i' => '\1_\2 integer index',
        '/^(position)$/i' => '\1 integer index',
        '/^(.+_at)$/i' => '\1 datetime',
        '/^(.+_on)$/i' => '\1 date',
        '/^(is_|has_|do_|does_|are_)([A-Z0-9_]+)$/i' => '\1\2 boolean not null default \'0\' index', //
        '/^([A-Z0-9_]+) *(\([0-9]+\))?$/i' => '\1 string\2', // Everything else will default to string
        '/^((.+ )string([^\(.]*))$/i' => '\2string(255)\3', // If we don't set the string length it will fail, so if not present will set it to 255
        );
    }

    protected function _castToMultilingualColumn($found, $column) {
        $columns = array();
        foreach (Ak::langs() as $lang){
            $columns[] = $lang.'_'.ltrim($column);
        }
        return $this->_setColumnDefaults($columns);
    }

    protected function _getColumnsToIndex($column_string) {
        $columns_to_index = array();
        foreach (explode(',',$column_string.',') as $column){
            if(preg_match('/["\'`]*([A-Za-z0-9_]+)["\'`]* (.*) (INDEX|UNIQUE|FULLTEXT|HASH) ?(.*)$/i',$column,$match)){
                $columns_to_index[$match[1]] = $match[3];
            }
        }
        return $columns_to_index;
    }

    protected function _getUniqueValueColumns($column_string) {
        $unique_columns = array();
        foreach (explode(',',$column_string.',') as $column){
            if(preg_match('/["\'`]*([A-Za-z0-9_]+)["\'`]* (.*) UNIQUE ?(.*)$/',$column,$match)){
                $unique_columns[] = $match[1];
            }
        }
        return $unique_columns;
    }

    protected function _requiresSequenceTable($column_string) {
        if(in_array($this->db->type(),array('mysql','postgre'))){
            return false;
        }
        foreach (explode(',',$column_string.',') as $column){
            if(preg_match('/["\'`]*([A-Za-z0-9_]+)["\'`]* (.*) AUTO (.*)$/',$column)){
                return true;
            }
            if(preg_match('/^["\'`]*id["\'`]* /',$column)){
                return true;
            }
        }
        return false;
    }

    protected function _ensureColumnNameCompatibility($columns) {
        $columns = explode(',',$columns.',');
        foreach ($columns as $column){
            $column = trim($column);
            $column = substr($column, 0, strpos($column.' ',' '));
            $this->_canUseColumn($column);
        }
    }

    protected function _canUseColumn($column_name) {
        $invalid_columns = $this->_getInvalidColumnNames();
        if(in_array($column_name, $invalid_columns)){

            $method_name_part = AkInflector::camelize($column_name);
            $method_name = (method_exists(new AkActiveRecord(), 'set'.$method_name_part)?'set':'get').$method_name_part;

            trigger_error(Ak::t('A method named %method_name exists in the AkActiveRecord class'.
            ' which will cause a recusion problem if you use the column %column_name in your database. '.
            'You can disable automatic %type by setting the constant %constant to false '.
            'in your configuration file.', array(
            '%method_name'=> $method_name,
            '%column_name' => $column_name,
            '%type' => Ak::t($method_name[0] == 's' ? 'setters' : 'getters'),
            '%constant' => Ak::t($method_name[0] == 's' ? 'ACTIVE_RECORD_ENABLE_CALLBACK_SETTERS' : 'ACTIVE_RECORD_ENABLE_CALLBACK_GETTERS'),
            ''
            )), E_USER_ERROR);
        }
    }

    protected function _getInvalidColumnNames() {
        return defined('INVALID_ACTIVE_RECORD_COLUMNS') ? explode(',',INVALID_ACTIVE_RECORD_COLUMNS) : array('sanitized_conditions_array','conditions','inheritance_column','inheritance_column',
        'subclasses','attribute','attributes','attribute','attributes','accessible_attributes','protected_attributes',
        'serialized_attributes','available_attributes','attribute_caption','primary_key','column_names','content_columns',
        'attribute_names','combined_subattributes','available_combined_attributes','connection','connection','primary_key',
        'table_name','table_name','only_available_atrributes','columns_for_atrributes','columns_with_regex_boundaries','columns',
        'column_settings','column_settings','rails_data_type','class_for_database_table_mapping','display_field','display_field',
        'internationalized_columns','available_locales','current_locale','attribute_by_locale','attribute_locales',
        'attribute_by_locale','attribute_locales','attributes_before_type_cast','attribute_before_type_cast','serialize_attribute',
        'available_attributes_quoted','attributes_quoted','column_type','value_for_date_column','observable_state',
        'observable_state','observers','errors','base_errors','errors_on','full_error_messages','array_from_ak_string',
        'attribute_condition','association_handler','associated','associated_finder_sql_options','association_option',
        'association_option','association_id','associated_ids','associated_handler_name','associated_type','association_type',
        'collection_handler_name','model_name','model_name','parent_model_name','parent_model_name');
    }


    /**
     * Migration version management functions
     */

    public function getInstalledVersion($options = array()) {
        try{
            $Migration = $this->RailsMigration->findFirstBy('name', $this->getInstallerName());
            $version = $Migration->version;
        } catch (RecordNotFoundException $e){
            $version_file = $this->_versionPath($options);
            if(!is_file($version_file)){
                $version = 0;
                $this->setInstalledVersion($version, $options);
            } else {
                $this->_removeOldVersionsFileAndUseMigrationsTable($options);
            }
        }
        $this->log('Installed version of '.$this->getInstallerName().':'.$version);
        return $version;
    }

    public function setInstalledVersion($version, $options = array()) {
        $this->log('Setting version of '.$this->getInstallerName().' to '.$version);
        try{
            $Migration = $this->RailsMigration->findFirstBy('name', $this->getInstallerName());
            $Migration->version = $version;
            return $Migration->save();
        } catch (RecordNotFoundException $e){
            return false != $this->RailsMigration->create(array('name' => $this->getInstallerName(), 'version' => $version));
        }
    }

    public function getInstallerName() {
        return str_replace('installer','',strtolower(get_class($this)));
    }

    public function ensureRailsMigrationsModelIsAvailable() {
        if(!class_exists('RailsMigration')){
            eval('class RailsMigration extends AkActiveRecord {}');
        }
        if(!isset($this->RailsMigration) || !($this->RailsMigration instanceof AkActiveRecord)){
            $this->RailsMigration = new RailsMigration();
        }
        $this->_createMigrationsTableIfNeeded();
        $this->RailsMigration->setConnection($this->db);
    }

    private function _versionPath($options = array()) {
        $mode = empty($options['mode']) ? ENVIRONMENT : $options['mode'];
        return $this->app_tmp_dir.DS.'installer_versions'.DS.(empty($this->module)?'':$this->module.DS).$mode.'_'.$this->getInstallerName().'_version.txt';
    }

    private function _createMigrationsTableIfNeeded() {
        if(!$this->tableExists('rails_migrations')) {
            AkDbSchemaCache::clearAll();
            $this->data_dictionary = $this->db->getDictionary();
            $this->available_tables = $this->getAvailableTables();
            $this->createTable('rails_migrations','id, name, version int');
            $this->addIndex('rails_migrations','UNIQUE name','unq_name');
        }
    }

    protected function _removeOldVersionsFileAndUseMigrationsTable($options) {
        $oldfile = $this->_versionPath($options);
        $version = AkFileSystem::file_get_contents($oldfile);
        AkFileSystem::copy($oldfile, $oldfile.'.backup');
        AkFileSystem::file_delete($oldfile);
        $this->log('message','got old version from file:'.$oldfile.'='.$version.' moved to backup-file:'.$oldfile.'.backup');
        $this->setInstalledVersion($version, $options);
        $this->RailsMigration->create(array('name' => $this->getInstallerName(), 'version' => $version));
    }

    public function getAvailableVersions() {
        $versions = array();
        foreach(get_class_methods($this) as $method_name){
            if(preg_match('/^up_([0-9]*)$/',$method_name, $match)){
                $versions[] = intval($match[1]);
            }
        }
        sort($versions);
        return $versions;
    }

    public function log($message, $type = '', $identifyer = '') {
        if (LOG_EVENTS){
            $Logger = Ak::getLogger();
            $Logger->info($message, $type);
        }
    }


    protected function _checkForCollisions(&$directory_structure, $base_path = null, $src_path = null) {
        foreach ($directory_structure as $k=>$node){
            if(!empty($this->skip_all)){
                return ;
            }
            if(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        if($this->_checkForCollisions($directory_structure[$k][$dir], $path, $src_path) === false){
                            $this->skip_all = true;
                            return;
                        }
                    }
                }
            }else{
                $original_file = $base_path.DS.$node;
                $new_file_location = $this->app_base_dir.str_replace($src_path, '', $original_file);
                if(is_file($new_file_location)){
                    $message = Ak::t('File %file exists.', array('%file'=>$new_file_location));
                    $user_response = AkConsole::promptUserVar($message."\n d (overwrite mine), i (keep mine), a (abort), O (overwrite all), K (keep all)", 'i');
                    if($user_response == 'i'){
                        unset($directory_structure[$k]);
                    }    elseif($user_response == 'O'){
                        return false;
                    }    elseif($user_response == 'K'){
                        $directory_structure = array();
                        return false;
                    }elseif($user_response != 'd'){
                        echo "\nAborting\n";
                        exit;
                    }
                }
            }
        }
    }

    protected function _checkForModified(&$directory_structure, $base_path = null, $src_path = null) {
        foreach ($directory_structure as $k=>$node){
            if(!empty($this->skip_all)){
                return ;
            }

            if(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        if($this->_checkForModified($directory_structure[$k][$dir], $path, $src_path) === false){
                            $this->skip_all = true;
                            return;
                        }
                    }
                }
            }else{
                $original_file = $base_path.DS.$node;
                $new_file_location = $this->app_base_dir.str_replace($src_path, '', $original_file);
                if(!file_exists($new_file_location)){
                    unset($directory_structure[$k]);
                }elseif(md5_file($new_file_location) != md5_file($original_file)){
                    $message = Ak::t('The file %file exists has local modifications.', array('%file'=>$new_file_location));
                    $user_response = AkConsole::promptUserVar($message."\n k (keep mine), d (delete), a (abort), D (delete all), K (keep all)", 'k');
                    if($user_response == 'k'){
                        unset($directory_structure[$k]);
                    }elseif($user_response == 'd'){
                    }elseif($user_response == 'D'){
                        return false;
                    } elseif($user_response == 'K'){
                        $directory_structure = array();
                        return false;
                    }elseif($user_response != 'd'){
                        echo "\nAborting\n";
                        exit;
                    }
                }
            }
        }
    }

    protected function _getInstalledFiles(&$directory_structure, $base_path = null, $src_path = null, $installed_files = array()) {
        foreach ($directory_structure as $k=>$node){
            if(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        $installed_files = array_merge($this->_getInstalledFiles($directory_structure[$k][$dir], $path, $src_path, $installed_files), $installed_files);
                    }
                }
            }else{
                $original_file = $base_path.DS.$node;
                $new_file_location = $this->app_base_dir.str_replace($src_path, '', $original_file);
                $installed_files[] = $new_file_location;
            }
        }
        return array_unique($installed_files);
    }

    protected function _copyFiles($directory_structure, $base_path = null, $src_path = null) {
        foreach ($directory_structure as $k=>$node){
	    if(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        echo 'Creating dir '.$path."\n";
                        $this->_makeDir($path, $src_path);
                        $this->_copyFiles($items, $path, $src_path);
                    }
                }
		continue;
            }else{
		$path = $base_path.DS.$node;
	    }
            if(is_dir($path)){
                echo 'Creating dir '.$path."\n";
                $this->_makeDir($path, $src_path);
            }elseif(is_file($path)){
                echo 'Creating file '.$path."\n";
                $this->_copyFile($path, $src_path);
            }
        }
    }

    protected function _makeDir($path, $base_path) {
        $dir = str_replace($base_path, $this->app_base_dir,$path);
        if(!is_dir($dir)){
            mkdir($dir);
        }
    }

    protected function _copyFile($path, $base_path) {
        $destination_file = str_replace($base_path, $this->app_base_dir,$path);
        copy($path, $destination_file);
        $source_file_mode =  fileperms($path);
        $target_file_mode =  fileperms($destination_file);
        if($source_file_mode != $target_file_mode){
            chmod($destination_file,$source_file_mode);
        }
    }

    public function copyFilesIntoApp($files_dir, $options = array()) {
        $this->files = AkFileSystem::dir($files_dir, array('recurse'=> true));
        empty($this->options['force']) ? $this->_checkForCollisions($this->files, $files_dir, $files_dir) : null;
        $this->_copyFiles($this->files, $files_dir, $files_dir);
        if(!empty($options['relative_url'])){
            $this->absolutizeStaticAssetFilePaths($files_dir, $options['relative_url']);
        }
    }

    public function removeFilesFromApp($files_dir) {
        $this->files = AkFileSystem::dir($files_dir, array('recurse'=> true));
        empty($this->options['force']) ? $this->_checkForModified($this->files, $files_dir, $files_dir) : null;
        $installed_files = $this->_getInstalledFiles($this->files, $files_dir, $files_dir);
        foreach ($installed_files as $installed_file){
            echo "Removing $installed_file\n";
            unlink($installed_file);
        }
        echo count($installed_files)." files removed\n";
    }

    public function absolutizeStaticAssetFilePaths($files_dir, $relative_url = '/') {
        if($relative_url == '/') return ;
        $relative_url = str_replace('//', '/', '/'.trim($relative_url, '/').'/');
        $replacements = array(
        'url(/images' => 'url('.$relative_url.'images',
        'url(\'/images' => 'url(\''.$relative_url.'images',
        'url("/images' => 'url("'.$relative_url.'images',
        'src="/images/' => 'src="'.$relative_url.'images/',
        'src=\'/images/' => 'src=\''.$relative_url.'images/',
        'src="/javascrips/' => 'src="'.$relative_url.'javascrips/',
        'src=\'/javascrips/' => 'src=\''.$relative_url.'javascrips/',
        'type="text/css" href="/stylesheets' => 'type="text/css" href="'.$relative_url.'stylesheets',
        'type=\'text/css\' href=\'/stylesheets' => 'type=\'text/css\' href=\''.$relative_url.'stylesheets',
        );
        $keys = array_keys($replacements);
        $values = array_values($replacements);
        $this->files = AkFileSystem::dir($files_dir, array('recurse'=> true));
        empty($this->options['force']) ? $this->_checkForModified($this->files, $files_dir, $files_dir) : null;
        $installed_files = $this->_getInstalledFiles($this->files, $files_dir, $files_dir);
        foreach ($installed_files as $installed_file){
            if(preg_match('/\.(js|css|html)$/', $installed_file)){
                $contents = str_replace($keys, $values, file_get_contents($installed_file));
                file_put_contents($installed_file, $contents);
            }
        }
    }

    /**
     * Shortcuts for running migrations.
     * 
     * This can be useful for running individual migraitons from your application_installer.php
     *  where you can use it like:
     *
     * 
     *     class ApplicationInstaller extends AkInstaller {
     *
     *       function up_2(){
     *          // Will run a migration located in the plugins directory
     *           $this->installMigration('Invitations', 1, APP_DIR.DS.'vendor'.DS.'plugins'.DS.'invitations'.DS.'installer');
     *       }
     *       
     *       function down_2(){
     *           $this->uninstallMigration('Invitations', null, APP_DIR.DS.'vendor'.DS.'plugins'.DS.'invitations'.DS.'installer');
     *       }
     *       
     *       function up_1() {
     *           $this->installMigration('Admin Plugin', 1); // Will install version 1 of the admin plugin
     *       }
     *       
     *       function down_1(){
     *           $this->uninstallMigration('Admin Plugin');
     *       }
     *     }
     */

    static function installMigration($installer_name, $version, $installer_base_path = null){
        $Installer = self::getInstallerInstance($installer_name, $installer_base_path);
        $Installer->up($version);
        if($Installer->transactionHasFailed()){
            $this->transactionFail();
        }
    }

    static function uninstallMigration($installer_name, $version = null, $installer_base_path = null){
        $Installer = self::getInstallerInstance($installer_name, $installer_base_path);
        $Installer->down($version);
        if($Installer->transactionHasFailed()){
            $this->transactionFail();
        }
    }

    static function getInstallerInstance($installer_name, $installer_base_path = null){

        $installer_file_name = (empty($installer_base_path)?AkConfig::getDir('app').DS.'installers'.DS:$installer_base_path.DS).AkInflector::underscore($installer_name).'_installer.php';

        if(!file_exists($installer_file_name)){
            trigger_error('Oops, the installer file '.$installer_file_name.' does not exist.', E_USER_ERROR);
        }
        include_once($installer_file_name);

        $installer_class_name = AkInflector::camelize($installer_name).'Installer';
        if(!class_exists($installer_class_name)){
            trigger_error('Oops, the installer class '.$installer_class_name.' does not exist.', E_USER_ERROR);
        }
        $Installer = new $installer_class_name();
        $Installer->vervose = $this->vervose;
        $Installer->use_transactions = false;
        return $Installer;
    }

}

