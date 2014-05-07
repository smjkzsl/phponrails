<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
* Charset conversion using UT8 mapping tables.
*
* Charset conversion using 4 different methods. Pure PHP
* conversion or one of this PHP extensions  iconv, recode and
* multibyte.
*
* Supported charsets are:
* ASCII, ISO 8859-1, ISO 8859-2, ISO 8859-3, ISO 8859-4, ISO
* 8859-5, ISO 8859-6, ISO 8859-7, ISO 8859-8, ISO 8859-9, ISO
* 8859-10, ISO 8859-11, ISO 8859-13, ISO 8859-14, ISO 8859-15,
* ISO 8859-16, CP437, CP737, CP850, CP852, CP855, CP857,
* CP858, CP860, CP861, CP863, CP865, CP866, CP869,
* Windows-1250, Windows-1251, Windows-1252, Windows-1253,
* Windows-1254, Windows-1255, Windows-1256, Windows-1257,
* Windows-1258, KOI8-R, KOI8-U, ISCII, VISCII, Big5, HKSCS,
* GB2312, GB18030, Shift-JIS, EUC
*
* More information about charsets at
* http://en.wikipedia.org/wiki/Character_encoding
*/
class AkCharset
{

    /**
	* Allow charset recoding.
	*
	* @access public
	* @var    bool    $enableCharsetRecoding
	*/
    public $enableCharsetRecoding = true;

    /**
	* Allow or disallow PHP Based charset conversion.
	*
	* @access public
	* @var    boolean    $usePhpRecoding
	*/
    public $usePhpRecoding = true;

    /**
	* Default charset
	*
	* @access public
	* @var    string    $defaultCharset
	*/
    public $defaultCharset = 'ISO-8859-1';

    /**
	* UTF-8 error character
	*
	* Char that will be used when no matches are found on the UTF8
	* mapping table
	*
	* @access public
	* @var    string    $utf8ErrorChar
	*/
    public $utf8ErrorChar = '?';

    /**
	* Current encoding engine
	*
	* @see getRecodingEngine
	* @see setRecodingEngine
	* @access private
	* @var    string    $_recodingEngine
	*/
    public $_recodingEngine = null;

    /**
	* Extra parameters for invoking the encoding engine (useful
	* for iconv)
	*
	* @see getRecodingEngineExtraParams
	* @see setRecodingEngineExtraParams
	* @access private
	* @var    string    $_recodingEngineExtraParams
	*/
    public $_recodingEngineExtraParams = '';

    /**
	* Holds current procesing charset.
	*
	* @see getCurrentCharset
	* @access private
	* @var    string    $_currentCharset
	*/
    public $_currentCharset = 'ISO-8859-1';

    /**
	* $this->_recodingEngine getter
	*
	* Use this method to get $this->_recodingEngine value
	*
	* @access public
	* @see set$recodingEngine
	* @return    string    Returns Current encoding engine value.
	*/
    public function getRecodingEngine() {
        return $this->_recodingEngine;

    }
    
    /**
	* $this->_recodingEngineExtraParams getter
	*
	* Use this method to get $this->_recodingEngineExtraParams
	* value
	*
	* @access public
	* @see set$recodingEngineExtraParams
	* @return    string    Returns Extra parameters for invoking the encoding
	* engine (useful for iconv) value.
	*/
    public function getRecodingEngineExtraParams() {
        return $this->_recodingEngineExtraParams;

    }
    
    /**
	* $this->_currentCharset getter
	*
	* Use this method to get $this->_currentCharset value
	*
	* @access public
	* @see set$currentCharset
	* @return    string    Returns Holds current procesing charset. value.
	*/
    public function getCurrentCharset() {
        return $this->_currentCharset;

    }
    
    /**
	* Sets the default recoding engine.
	*
	* @access public
	* @uses _loadExtension
	* @param    string    $engine    Possible engines are:
	* - iconv (http://php.net/iconv)
	* - mbstring (http://php.net/mb_convert_encoding)
	* - recode (http://php.net/recode_string)
	* @param    string    $extra_params    Extra parameters for invoking the encoding engine
	* (useful for iconv)
	* @return    string    Name of current recoding engine
	*/
    public function setRecodingEngine($engine = null, $extra_params = null) {
        static $memory;

        if(isset($memory[$engine.$extra_params])){
            return $memory[$engine.$extra_params];
        }

        $engines = array('iconv'=>'iconv','mbstring'=>'mb_convert_encoding','recode'=>'recode_string');
        $this->_recodingEngine = false;
        // Fix for systems with constant iconv defined. Php uses libiconv function instead
        if (!function_exists('iconv') && function_exists('libiconv')) {
            function iconv($input_encoding, $output_encoding, $string) {
                return libiconv($input_encoding, $output_encoding, $string);
            }
        }
        if(empty($engine)){
            foreach ($engines as $_engine=>$function){
                if(@function_exists($function)){
                    $this->_recodingEngine = $_engine;
                    break;
                }elseif($this->_loadExtension($_engine) && function_exists($function)){
                    $this->_recodingEngine = $_engine;
                    break;
                }
            }
        }elseif (isset($engines[$engine])){
            if(!@function_exists($engines[$engine])){
                user_error(Ak::t('Could not set AkCharset::setRecodingEngine("%engine");',array('%engine'=>$engine)),E_USER_NOTICE);
                $memory[$engine.$extra_params] = false;
            }else{
                $this->_recodingEngine = $engine;
            }
        }
        if(isset($extra_params)){
            $this->_recodingEngineExtraParams = $extra_params;
        }
        $memory[$engine.$extra_params] = $this->_recodingEngine;
        return $this->_recodingEngine;
    }
    
    /**
	* $this->_recodingEngineExtraParams setter
	*
	* Use this method to set $this->_recodingEngineExtraParams
	* value
	*
	* @access public
	* @see get$recodingEngineExtraParams
	* @param    string    $recoding__engine__extra__params    Extra parameters for invoking the encoding engine
	* (useful for iconv)
	* @return    bool    Returns true if $this->_recodingEngineExtraParams
	* has been set correctly.
	*/
    public function setRecodingEngineExtraParams($recoding__engine__extra__params) {
        $this->_recodingEngineExtraParams = $recoding__engine__extra__params;

    }
    
    /**
	* Changes the charset encoding of one string to other charset.
	*
	* This function will convert a string from one charset to
	* another.
	* Unfortunately PHP has not native Unicode support, so in
	* order to display and handle different charsets, this
	* function wraps 3 non standard PHP extensions plus an
	* additional Pure PHP conversion utility for systems that do
	* not have this extensions enabled.
	*
	* @access public
	* @param    string    $string    String to recode
	* @param    string    $target_charset    Target charset. AkCharset availability may vary
	* depending on your system configuration.
	* @param    string    $origin_charset    Input string charset. AkCharset availability may
	* vary depending on your system configuration.
	* This parameter is optional if you are using
	* multibyte extension.
	* @param    string    $engine    Possible engines are:
	* - iconv (http://php.net/iconv)
	* - mbstring (http://php.net/mb_convert_encoding)
	* - recode (http://php.net/recode_string)
	* @param    string    $engine_extra_params    Extra parameters for invoking the encoding engine
	* (useful for iconv)
	* @return    void    Recoded string if possible, otherwise it will
	* return the string without modifications.
	*/
    public function recodeString($string, $target_charset, $origin_charset = null, $engine = null, $engine_extra_params = null) {
        static $memory;
        if(!is_string($string)){
            return $string;
        }
        if($this->enableCharsetRecoding == false || $target_charset==$origin_charset){
            return $string;
        }
        if(isset($engine) || !isset($memory['engine'])){
            $engine = $memory['engine'] = $this->setRecodingEngine($engine,$engine_extra_params);
        }else{
            $engine = $memory['engine'];
        }
        if(!$engine && !$this->usePhpRecoding){
            return $string;
        }
        $method = strlen($engine) > 1 ? '_'.$engine.'StringRecode' : '_phpStringRecode';

        if(method_exists($this,$method)){
            return $this->$method($string, $target_charset, $origin_charset, $engine_extra_params);
        }else{
            user_error(Ak::t('Could not invoque AkCharset::%method();',array('%method'=>$method)),E_USER_NOTICE);
            return $string;
        }
    }

    /**
	* Fetch an array with UTF8 charset equivalence table.
	*
	* @access public
	* @uses _LoadInverseMap
	* @uses _getCharset
	* @param    string    $charset    Desired charset
	* @return    mixed    Multilevel array with selected mapping:
	* array(
	* 'to_utf' => array(CHARS_VAL=>UTF_VAL),
	* 'from_utf' => array(UTF_VAL=>CHARS_VAL)
	* );
	*
	* False if mapping is not found.
	*/
    public function getMapping($charset) {
        $charset = $this->_getCharset($charset,false);
        if($charset!=false){
            $mapping = array();
            require_once(ACTIVE_SUPPORT_DIR.DS.'i18n'.DS.'charset'.DS.'utf8_mappings'.DS.$charset.'.php');

            if(class_exists($charset)){
                $mappingObject =& Ak::singleton($charset,$charset);
                $mapping["to_utf"] = $mappingObject->_toUtfMap;
                $mappingObject->_LoadInverseMap();
                $mapping["from_utf"] = $mappingObject->_fromUtfMap;

                return $mapping;
            }
        }
        return false;
    }

    /**
	* Tries to load required extension.
	*
	* @access private
	* @see setRecodingEngine
	* @param    string    $extension    Extension name
	* @return    boolean    Returns true on success false on failure.
	*/
    protected function _loadExtension($extension) {
        static $memory;
        if(!isset($memory[$extension])){
            if (!extension_loaded($extension)) {
                if(!ini_get('safe_mode')){
                    $prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
                    $memory[$extension] = @dl($prefix .$extension.PHP_SHLIB_SUFFIX);
                }else{
                    $memory[$extension] = false;
                }
            }else{
                $memory[$extension] = true;
            }
        }
        return $memory[$extension];
    }

    /**
	* AkCharset::recodeString() iconv implementation
	*
	* @access private
	* @see recodeString
	* @return    string    Recoded string if possible, otherwise it will
	* return the string without modifications.
	*/
    protected function _iconvStringRecode($string, $target_charset, $origin_charset, $engine_extra_params=null) {
        if(!$this->_conversionIsNeeded($origin_charset, $target_charset) && !$this->isUtf8($string)){
            return $string;
        }

        $skip_combinations = array('ISO-8859-1.UTF-8', 'UTF-8.ISO-8859-1');
        if(in_array($target_charset.'.'.$origin_charset, $skip_combinations)){
            return $this->_phpStringRecode($string, $target_charset, $origin_charset);
        }

        $engine_extra_params = isset($engine_extra_params) ? $engine_extra_params : $this->_recodingEngineExtraParams;
        if(!$result = @iconv($target_charset, $origin_charset.$engine_extra_params, $string)){
            return $this->_phpStringRecode($string, $target_charset, $origin_charset);
        }else{
            return $result;
        }
    }

    /**
	* AkCharset::recodeString() recode_string implementation
	*
	* @access private
	* @see recodeString
	* @return    string    Recoded string if possible, otherwise it will
	* return the string without modifications.
	*/
    protected function _recodeStringRecode($string, $target_charset, $origin_charset) {
        return recode_string($target_charset, '..'.$origin_charset, $string);
    }// -- end of &_recodeStringRecode -- //

    /**
	* AkCharset::recodeString() mb_convert_encoding implementation
	*
	* @access private
	* @see recodeString
	* @return    string    Recoded string if possible, otherwise it will
	* return the string without modifications.
	*/
    protected function _mbstringStringRecode($string, $target_charset, $origin_charset=null) {
        if(is_null($origin_charset)){
            $origin_charset = $string;
        }else{
            if(!$this->_conversionIsNeeded($origin_charset, $target_charset) && !$this->isUtf8($string)){
                return $string;
            }
        }
        $origin_charset = empty($origin_charset) ? mb_detect_encoding($string) : $origin_charset;
        if(!@mb_check_encoding('', $origin_charset) || !@mb_check_encoding('', $target_charset)){
            $result = $this->_phpStringRecode($string, $target_charset, $origin_charset);
        }else{
            $result = mb_convert_encoding($string,$target_charset, $origin_charset);
        }
        return $result;
    }
    
    /**
	* AkCharset::recodeString() Pure PHP implementation
	*
	* @access private
	* @uses _utf8StringEncode
	* @uses _utf8StringDecode
	* @see recodeString
	* @see _utf8StringEncode
	* @see _utf8StringDecode
	* @return    string    Recoded string if possible, otherwise it will
	* return the string without modifications.
	*/
    protected function _phpStringRecode($string, $target_charset, $origin_charset) {
        $target_charset = $this->_getCharset($target_charset, false);
        $origin_charset = $this->_getCharset($origin_charset, false);

        if((!$target_charset || !$origin_charset) || ((!$this->_conversionIsNeeded($origin_charset, $target_charset) || !$this->usePhpRecoding) && !$this->isUtf8($string))){
            return $string;
        }
        if($origin_charset=='utf8'){
            require_once(ACTIVE_SUPPORT_DIR.DS.'i18n'.DS.'charset'.DS.'utf8_mappings'.DS.$target_charset.'.php');
            if(class_exists($target_charset)){

                $mappingObject =& Ak::singleton($target_charset, $target_charset);

                if(method_exists($mappingObject,'_utf8StringDecode')){
                    return $mappingObject->_utf8StringDecode($string);
                }else{
                    return $string;
                }
            }else{
                return $string;
            }
        }elseif($target_charset=='utf8'){
            require_once(ACTIVE_SUPPORT_DIR.DS.'i18n'.DS.'charset'.DS.'utf8_mappings'.DS.$origin_charset.'.php');
            if(class_exists($origin_charset)){
                $mappingObject =& Ak::singleton($origin_charset, $origin_charset);
                if(method_exists($mappingObject,'_utf8StringEncode')){
                    return $mappingObject->_utf8StringEncode($string);
                }else{
                    return $string;
                }
            }else{
                return $string;
            }
        }else{
            $utf8String = $this->_phpStringRecode($string,'utf8',$origin_charset);
            return $this->_phpStringRecode($utf8String,$target_charset,'utf8');
        }
    }
    
    /**
	* Checks for possibility or need of charset conversion.
	*
	* @access private
	* @uses _getCharset
	* @param    string    $origin_charset
	* @param    string    $target_charset
	* @return    boolean
	*/
    protected function _conversionIsNeeded($origin_charset, $target_charset) {
        $target_charset = $this->_getCharset($target_charset,false);
        $origin_charset = $this->_getCharset($origin_charset,false);

        if(($origin_charset==$target_charset)||!$target_charset||!$origin_charset){
            return false;
        }

        if($origin_charset == 'utf8' || $target_charset == 'utf8'){
            return true;
        }
        $similar_charsets[] = array('cp1257','iso885913','iso88594');
        $similar_charsets[] = array('koi8u','cp1251','iso88595','koi8r');

        foreach ($similar_charsets as $group){
            if(in_array($origin_charset,$group)&&in_array($target_charset,$group)){
                return true;
            }
        }
        return false;
    }
    
    /**
	* Filters input charset and returns a custom formated value
	* for class wide usage.
	*
	* @access private
	* @param    string    $charset    AkCharset name
	* @param    boolean    $set_charset    If true will set $this->defaultCharset value
	* @return    mixed    AkCharset internal name or FALSE if charset is not
	* found.
	*/
    protected function _getCharset($charset = null, $set_charset = true) {
        static $memory;
        if(isset($memory[$charset])){
            return $memory[$charset];
        }

        $procesed_charset = $charset == null ? $this->defaultCharset : $charset;
        $procesed_charset = str_replace(array('-','_','.',' '),'',strtolower(trim($procesed_charset)));
        $procesed_charset = str_replace(array('windows','ibm'),'cp',strtolower(trim($procesed_charset)));
        $alias_xref = array('437'=>'cp437','850'=>'cp850','852'=>'cp852','855'=>'cp855','857'=>'cp857',
        '860'=>'cp860','861'=>'cp861','862'=>'cp862','863'=>'cp863','865'=>'cp865','866'=>'cp866','869'=>'cp869',
        'ansix341968'=>'ascii','ansix341986'=>'ascii','arabic'=>'iso88596','asmo708'=>'iso88596','big5cp950'=>'big5',
        'cp367'=>'ascii','cp819'=>'iso88591','cpgr'=>'cp869','cpis'=>'cp861','csascii'=>'ascii','csbig5'=>'big5',
        'cscp855'=>'cp855','cscp857'=>'cp857','cscp860'=>'cp860','cscp861'=>'cp861','cscp863'=>'cp863','cscp864'=>'cp864',
        'cscp865'=>'cp865','cscp866'=>'cp866','cscp869'=>'cp869','cseuckr'=>'euckr','cseucpkdfmtjapanese'=>'eucjp',
        'csgb2312'=>'gb18030','csisolatin1'=>'iso88591','csisolatin2'=>'iso88592','csisolatin3'=>'iso88593',
        'csisolatin4'=>'iso88594','csisolatin5'=>'iso88599','csisolatinarabic'=>'iso88596',
        'csisolatincyrillic'=>'iso88595','csisolatingreek'=>'iso88597','csisolatinhebrew'=>'iso88598','cskoi8r'=>'koi8r',
        'cspc850multilingual'=>'cp850','cspc862latinhebrew'=>'cp862','cspc8codepage437'=>'cp437','cspcp852'=>'cp852',
        'csshiftjis'=>'shiftjis','cyrillic'=>'iso88595','ecma114'=>'iso88596','ecma118'=>'iso88597','elot928'=>'iso88597',
        'extendedunixcodepackedformatforjapanese'=>'eucjp','gb2312'=>'gb18030','greek'=>'iso88597','greek8'=>'iso88597',
        'hebrew'=>'iso88598','hkscsbig5'=>'big5hkscs','iso646irv:1991'=>'ascii','iso646us'=>'ascii',
        'iso885914:1998'=>'iso885914','iso88591:1987'=>'iso88591','iso88592:1987'=>'iso88592','iso88593:1988'=>'iso88593',
        'iso88594:1988'=>'iso88594','iso88595:1988'=>'iso88595','iso88596:1987'=>'iso88596','iso88597:1987'=>'iso88597',
        'iso88598:1988'=>'iso88598','iso88599:1989'=>'iso88599','isoceltic'=>'iso885914','isoir100'=>'iso88591',
        'isoir101'=>'iso88592','isoir109'=>'iso88593','isoir110'=>'iso88594','isoir126'=>'iso88597','isoir127'=>'iso88596',
        'isoir138'=>'iso88598','isoir144'=>'iso88595','isoir148'=>'iso88599','isoir166'=>'tis620','isoir179'=>'iso885913',
        'isoir199'=>'iso885914','isoir226'=>'iso885916','isoir6'=>'ascii','l1'=>'iso88591','l10'=>'iso885916','l2'=>'iso88592',
        'l3'=>'iso88593','l4'=>'iso88594','l5'=>'iso88599','l7'=>'iso885913','l8'=>'iso885914','latin1'=>'iso88591',
        'latin10'=>'iso885916','latin2'=>'iso88592','latin3'=>'iso88593','latin4'=>'iso88594','latin5'=>'iso88599',
        'latin7'=>'iso885913','latin8'=>'iso885914','mscyrl'=>'cp1251','mshebr'=>'cp1255','mskanji'=>'shiftjis',
        'sjis'=>'shiftjis','tcabig5'=>'big5','tis6200'=>'tis620','tis62025291'=>'tis620','tis62025330'=>'tis620',
        'us'=>'ascii','usascii'=>'ascii');
        $alias = array(
        'armscii8'=>'armscii_8','ascii'=>'ascii','big5hkscs'=>'big5_hkscs','utf8'=>'utf8',
        'big5'=>'big5','cp1046'=>'cp1046','cp1124'=>'cp1124','cp1125'=>'cp1125','cp1129'=>'cp1129',
        'cp1133'=>'cp1133','cp1161'=>'cp1161','cp1162'=>'cp1162','cp1163'=>'cp1163','cp1250'=>'cp1250',
        'cp1251'=>'cp1251','cp1252'=>'cp1252','cp1253'=>'cp1253','cp1254'=>'cp1254','cp1255'=>'cp1255',
        'cp1256'=>'cp1256','cp1257'=>'cp1257','cp1258'=>'cp1258','cp437'=>'cp437','cp737'=>'cp737',
        'cp775'=>'cp775','cp850'=>'cp850','cp852'=>'cp852','cp853'=>'cp853','cp855'=>'cp855','cp856'=>'cp856',
        'cp857'=>'cp857','cp858'=>'cp858','cp860'=>'cp860','cp861'=>'cp861','cp862'=>'cp862','cp863'=>'cp863',
        'cp864'=>'cp864','cp865'=>'cp865','cp866'=>'cp866','cp869'=>'cp869','cp874'=>'cp874','cp922'=>'cp922',
        'cp932'=>'cp932','cp949'=>'cp949','cp950'=>'cp950','dechanyu'=>'dec_hanyu','deckanji'=>'dec_kanji',
        'euccn'=>'euc_cn','eucjisx0213'=>'euc_jisx0213','eucjp'=>'euc_jp','euckr'=>'euc_kr','euctw'=>'euc_tw',
        'gb18030'=>'gb18030','gbk'=>'gbk','georgianacademy'=>'georgian_academy','georgianps'=>'georgian_ps',
        'hproman8'=>'hp_roman8','iso88591'=>'iso_8859_1','iso885910'=>'iso_8859_10','iso885913'=>'iso_8859_13',
        'iso885914'=>'iso_8859_14','iso885915'=>'iso_8859_15','iso885916'=>'iso_8859_16','iso88592'=>'iso_8859_2',
        'iso88593'=>'iso_8859_3','iso88594'=>'iso_8859_4','iso88595'=>'iso_8859_5','iso88596'=>'iso_8859_6',
        'iso88597'=>'iso_8859_7','iso88598'=>'iso_8859_8','iso88599'=>'iso_8859_9','isoir165'=>'iso_ir_165',
        'iso646cn'=>'iso646_cn','iso646jp'=>'iso646_jp','jisx0201'=>'jis_x0201','johab'=>'johab','koi8r'=>'koi8_r',
        'koi8ru'=>'koi8_ru','koi8t'=>'koi8_t','koi8u'=>'koi8_u','macarabic'=>'macarabic',
        'maccentraleurope'=>'maccentraleurope','maccroatian'=>'maccroatian','maccyrillic'=>'maccyrillic',
        'macgreek'=>'macgreek','machebrew'=>'machebrew','maciceland'=>'maciceland','macroman'=>'macroman',
        'macromania'=>'macromania','macthai'=>'macthai','macturkish'=>'macturkish','macukraine'=>'macukraine',
        'mulelao1'=>'mulelao_1','nextstep'=>'nextstep','riscoslatin1'=>'riscos_latin1','shiftjis'=>'shift_jis',
        'shiftjisx0213'=>'shift_jisx0213','tcvn'=>'tcvn','tds565'=>'tds565','tis620'=>'tis_620','viscii'=>'viscii',
        'iso885911'=>'iso_8859_11', 'jis0228' => 'jis_0228', 'jis0212' => 'jis_0212'
        );
        $procesed_charset = isset($alias_xref[$procesed_charset]) ? $alias_xref[$procesed_charset] : $procesed_charset;
        $memory[$charset] = isset($alias[$procesed_charset]) ? $alias[$procesed_charset] : false;
        if($set_charset){
            $this->_currentCharset = $memory[$charset];
        }

        return $memory[$charset];
    }
    
    /**
	* Encodes given string as UTF8 text.
	*
	* Given string and charset mapping, returns input string as
	* UTF8 text
	*
	* @access private
	* @uses _charToUtf8
	* @see _phpStringRecode
	* @see _utf8StringDecode
	* @param    string    $string    Text to be converted to UTF8
	* @param    array    $mapping_array    Array containing the charset mapping.
	* @return    string    UTF8 String
	*/
    protected function _utf8StringEncode($string, $mapping_array = array()) {
        $chars = unpack('C*', $string);
        $count = count($chars);
        for($i=1;$i<=$count;$i++){
            if(!isset($mapping_array[$chars[$i]])){
                continue;
            }else{
                $char = (int)$mapping_array[$chars[$i]];
            }
            $chars[$i] = $this->_charToUtf8($char);
        }
        return implode('',$chars);
    }
    
    /**
	* Decodes data, assumed to be UTF-8 encoded given its
	* equivalence map.
	*
	* @access private
	* @uses _Utf8
	* @uses ToChar
	* @see _phpStringRecode
	* @see _utf8StringEncode
	* @param    string    $utf_string    UTF8 string
	* @param    array    $mapping_array    Mapping array
	* @return    string    Decoded string
	*/
    protected function _utf8StringDecode($utf_string, $mapping_array = array()) {
        $chars = unpack('C*', $utf_string);
        $count = count($chars);
        $result = '';
        for ($i=1;$i<=$count;$i++){
            $result .= $this->_utf8ToChar($chars,$i,$mapping_array);
        }
        return $result;
    }

    /**
	* Converts a single character to its UTF8 representation
	*
	* @access protected
	* @see _utf8StringEncode
	* @param    string    $char    Char to be converted
	* @return    string    UTF8 char
	*/
    protected function _charToUtf8($char) {
        if ($char < 0x80){
            $utf8_char = chr($char);
            // 2 bytes
        }else if($char<0x800){
            $utf8_char = (chr(0xC0 | $char>>6) . chr(0x80 | $char & 0x3F));
            // 3 bytes
        }else if($char<0x10000){
            $utf8_char = (chr(0xE0 | $char>>12) . chr(0x80 | $char>>6 & 0x3F) . chr(0x80 | $char & 0x3F));
            // 4 bytes
        }else if($char<0x200000){
            $utf8_char = (chr(0xF0 | $char>>18) . chr(0x80 | $char>>12 & 0x3F) . chr(0x80 | $char>>6 & 0x3F) . chr(0x80 | $char & 0x3F));
        }
        return $utf8_char;
    }

    /**
	* Decodes a single UTF8 char to it's representation as
	* specified in the mapping array
	*
	* @access private
	* @see _utf8StringDecode
	* @param    array    $chars    Assoc array with chars to be decoded
	* @param    integer    &$id    Current char position
	* @param    array    $mapping_array    Mapping Array
	* @return    string    Decoded char
	*/
    protected function _utf8ToChar($chars, &$id, $mapping_array) {
        if(($chars[$id]>=240)&&($chars[$id]<=255)){
            $utf=(intval($chars[$id]-240)<<18)+(intval($chars[++$id]-128)<<12)+(intval($chars[++$id]-128)<<6)+(intval($chars[++$id]-128)<<0);
        }elseif(($chars[$id]>=224)&&($chars[$id]<=239)){
            $utf=(intval($chars[$id]-224)<<12)+(intval($chars[++$id]-128)<<6)+(intval($chars[++$id]-128)<<0);
        }elseif(($chars[$id]>=192)&&($chars[$id]<=223)){
            $utf=(intval($chars[$id]-192)<<6)+(intval($chars[++$id]-128)<<0);
        }else{
            $utf=$chars[$id];
        }
        if(array_key_exists($utf,$mapping_array)){
            return chr($mapping_array[$utf]);
        }else{
            return $this->utf8ErrorChar;
        }
    }

    public function isUtf8($text = '') {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        return preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $text);
    }

    protected function _charsetMapFileExists($charset) {
        if(!file_exists(ACTIVE_SUPPORT_DIR.DS.'i18n'.DS.'charset'.DS.'utf8_mappings'.DS.$charset.'.php')){
            trigger_error(Ak::t('Charset %charset is not supported on your current setting. Please download aditional charset maps from http://svn.rails.org/extras/utf8_mappings/ into lib/AkActionView/utf8_mappings', array('%charset'=>$charset)), E_USER_NOTICE);
            return false;
        }
        return true;
    }
}

