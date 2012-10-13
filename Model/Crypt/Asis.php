<?php
/**
 * 
 * @desc Возвращает строку как есть.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Asis extends Crypt_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::decode()
	 */
	public function decode ($input, $key = null)
	{
		return $input;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key = null)
	{
		return $input;
	}
	
}