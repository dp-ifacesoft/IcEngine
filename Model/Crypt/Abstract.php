<?php
/**
 * 
 * @desc Абстрактный класс шифрования
 * @author Юрий Шведов
 *
 */
abstract class Crypt_Abstract
{
	
	/**
	 * @desc Метод дешифрования. Может быть реализован не для всех алгоритмов.
	 * @param string $input
	 * @param string $key [optional] Ключ дешифрования (если необходим).
	 */
	public function decode ($input, $key = null)
	{
		return null;
	}
	
	/**
	 * @desc Кодирование содержимого.
	 * @param string $input Содержимое для шифрования.
	 * @param string $key [optional] Ключ шифрования (если необходим).
	 */
	abstract public function encode ($input, $key = null);
	
}