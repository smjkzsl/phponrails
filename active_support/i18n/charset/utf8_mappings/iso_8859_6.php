<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
* ISO-8859-6  driver for Charset Class
*
* Charset::iso_8859_6 provides functionality to convert
* ISO-8859-6 strings, to UTF-8 multibyte format and vice versa.
*/
class iso_8859_6 extends AkCharset
{
	/**
	* ISO-8859-6 to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	protected $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>128,129=>129,130=>130,131=>131,132=>132,133=>133,134=>134,135=>135,136=>136,137=>137,138=>138,139=>139,140=>140,141=>141,142=>142,143=>143,144=>144,145=>145,146=>146,147=>147,148=>148,149=>149,150=>150,151=>151,152=>152,153=>153,154=>154,155=>155,156=>156,157=>157,158=>158,159=>159,160=>160,164=>164,172=>1548,173=>173,187=>1563,191=>1567,193=>1569,194=>1570,195=>1571,196=>1572,197=>1573,198=>1574,199=>1575,200=>1576,201=>1577,202=>1578,203=>1579,204=>1580,205=>1581,206=>1582,207=>1583,208=>1584,209=>1585,210=>1586,211=>1587,212=>1588,213=>1589,214=>1590,215=>1591,216=>1592,217=>1593,218=>1594,224=>1600,225=>1601,226=>1602,227=>1603,228=>1604,229=>1605,230=>1606,231=>1607,232=>1608,233=>1609,234=>1610,235=>1611,236=>1612,237=>1613,238=>1614,239=>1615,240=>1616,241=>1617,242=>1618);
		

	/**
	*  UTF-8 to ISO-8859-6 mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	protected $_fromUtfMap = null;
	/**
	* Encodes given ISO-8859-6 string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string ISO-8859-6 string
	* @return    string    UTF-8 string data
	*/
	protected function _utf8StringEncode($string, $mapping_array = array())
	{
		return parent::_utf8StringEncode($string, $this->_toUtfMap);
	
	}

	/**
	* Decodes given UFT-8 string into ISO-8859-6
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    ISO-8859-6 string data
	*/
	protected function _utf8StringDecode($string, $mapping_array = array())
	{
		$this->_LoadInverseMap();
		return parent::_utf8StringDecode($string, $this->_fromUtfMap);
	}
	
	/**
	* Flips $this->_toUtfMap to $this->_fromUtfMap
	*
	* @access private
	* @return	null
	*/
	protected function _LoadInverseMap()
	{
		static $loaded;
		if(!isset($loaded)){
			$loaded = true;
			$this->_fromUtfMap = array_flip($this->_toUtfMap);
		}
	}
	
}
