<?php

/**
 * Стандартный DES.
 * 
 * @author goorus
 */
class Crypt_Des extends Crypt_Abstract
{
	/**
     * Ключ
     * 
     * @var string
     */
	protected static $desKey;
	
    /**
     * Смещение
     * 
     * @var string
     */
	protected static $_desIV;
	
    /**
     * Конструктор
     */
	public function __construct()
	{
		self::$desKey = chr(99).chr(78).chr(99).chr(78).chr(99).
            chr(78).chr(99).chr(78);
		self::$desIV = self::$desKey;
	}
	
	/**
	 * @inheritdoc
	 */
	public function decode($input, $key = null)
	{
		$td = mcrypt_module_open('des', '', 'cbc', '');
		mcrypt_generic_init($td, self::$desKey, self::$desIV);
		$dec_data = mdecrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $dec_data;
	}
	
	/**
	 * @inheritdoc
	 */
	public function encode($input, $key = null)
	{
		$td = mcrypt_module_open('des', '', 'cbc', '');
		mcrypt_generic_init($td, self::$desKey, self::$desIV);
		$enc_data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $enc_data;
	}
}