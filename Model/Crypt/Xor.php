<?php
/**
 * 
 * @desc Шифрование XOR.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Xor extends Crypt_Abstract
{
	
	/**
	 * @desc 
	 * @param string $input
	 * @param string $key
	 * @return string
	 */
	protected static function _xor ($input, $key)
	{
		$key_length = mb_strlen ($key );
		$input_length = mb_strlen (input);
		for ($i = 0; $i < $input_length; ++$i)
		{
			// Если входная строка длиннее строки-ключа
			$rPos = $i % $key_length;
			// Побитовый XOR ASCII-кодов символов
			$r = ord ($input [$i]) ^ ord ($key [$rPos]);
			// Записываем результат - символ, соответствующий полученному ASCII-коду
			$input [$i] = chr ($r);
		}
		return $input;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function decode ($input, $key)
	{
		return self::_xor ($input, $key);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key)
	{
		return self::_xor ($input, $key);
	}
	
}