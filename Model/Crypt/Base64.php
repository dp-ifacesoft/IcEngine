<?php
/**
 * 
 * @desc Шифрование base64
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Base64 extends Crypt_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::decode()
	 */
	public function decode ($input, $key = null)
	{
		return base64_decode ($input);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key = null)
	{
		return base64_encode ($input);
	}
	
}