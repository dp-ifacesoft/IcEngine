<?php

/**
 * Возвращает строку как есть
 * 
 * @author goorus
 */
class Crypt_Asis extends Crypt_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function decode($input, $key = null)
	{
		return $input;
	}
	
	/**
	 * @inheritdoc
	 */
	public function encode($input, $key = null)
	{
		return $input;
	}
	
}