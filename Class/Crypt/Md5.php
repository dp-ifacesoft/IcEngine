<?php

/**
 * Шифрование md5.
 * 
 * @author goorus
 */
class Crypt_Md5 extends Crypt_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function encode($input, $key = null)
	{
		return md5($input);
	}
}