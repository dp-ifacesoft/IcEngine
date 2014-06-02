<?php
/**
 * 
 * @desc Профиль шифрования
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Profile extends Crypt_Abstract
{
	
	/**
	 * @desc
	 * @var array
	 */
	protected $_encode;
	
	/**
	 * @desc 
	 * @var array
	 */
	protected $_decode;
	
	/**
	 * @desc 
	 * @param array $encode Методы для шифрования.
	 * @param array $decode Методы для дешифрования.
	 */
	public function __construct ($encode, $decode)
	{
		$this->_encode = $encode ? (array) $encode : array ();
		$this->_decode = $decode ? (array) $decode : array ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::decode()
	 */
	public function decode ($input, $key = null)
	{
		foreach ($this->_decode as $name)
		{
			$input = Crypt_Manager::decode ($name, $input, $key);
		}
		return $input;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key = null)
	{
		foreach ($this->_encode as $name)
		{
			$input = Crypt_Manager::encode ($name, $input, $key);
		}
		return $input;
	}
	
}