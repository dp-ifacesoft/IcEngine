<?php

/**
 * Шифрование base64
 * 
 * @author goorus, morph
 */
class Crypt_Base64 extends Crypt_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function decode($input, $key = null)
	{
		return base64_decode($input);
	}
	
	/**
	 * @inheritdoc
	 */
	public function encode($input, $key = null)
	{
		return base64_encode($input);
	}
	
}