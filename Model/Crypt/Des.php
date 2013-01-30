<?php
/**
 * 
 * @desc Стандартный DES.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Des extends Crypt_Abstract
{
	
	protected static $_desKey;
	
	protected static $_desIV;
	
	public function __construct ()
	{
		self::$_desKey = chr(99).chr(78).chr(99).chr(78).chr(99).chr(78).chr(99).chr(78);
		self::$_desIV = chr(99).chr(78).chr(99).chr(78).chr(99).chr(78).chr(99).chr(78);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::decode()
	 */
	public function decode ($input, $key = null)
	{
		$td = mcrypt_module_open ('des', '', 'cbc', '');
		mcrypt_generic_init ($td, self::$_desKey, self::$_desIV);
		$dec_data = mdecrypt_generic($td, $input);
		mcrypt_generic_deinit ($td);
		mcrypt_module_close ($td);
		return $dec_data;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key = null)
	{
		$td = mcrypt_module_open ('des', '', 'cbc', '');
		mcrypt_generic_init ($td, self::$_desKey, self::$_desIV);
		$enc_data = mcrypt_generic ($td, $input);
		mcrypt_generic_deinit ($td);
		mcrypt_module_close ($td);
		return $enc_data;
	}
	
}