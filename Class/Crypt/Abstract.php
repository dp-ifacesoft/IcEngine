<?php

/**
 * Абстрактный класс шифрования
 * 
 * @author goorus, morph
 */
abstract class Crypt_Abstract
{
	/**
	 * Метод дешифрования. Может быть реализован не для всех алгоритмов.
	 * 
     * @param string $input
	 * @param string $key [optional] Ключ дешифрования (если необходим).
	 */
	public function decode($input, $key = null)
	{
		return null;
	}
	
	/**
	 * Кодирование содержимого.
	 * 
     * @param string $input Содержимое для шифрования.
	 * @param string $key [optional] Ключ шифрования (если необходим).
	 */
	abstract public function encode ($input, $key = null);
	
}