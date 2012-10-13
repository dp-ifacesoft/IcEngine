<?php
/**
 * 
 * @desc Шифрование md5.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Md5 extends Crypt_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key = null)
	{
		return md5 ($input);
	}
	
}