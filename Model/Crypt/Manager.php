<?php
/**
 *
 * @desc Менеджер алгоритмов шифрования.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Manager
{

	/**
	 * @desc Разделитель для провайла
	 * @var string
	 */
	const PROFILE_DELIMETER = '://';

	/**
	 * @desc Загруженные алгоритмы шифрования.
	 * @var array
	 */
	protected static $_crypts = array ();

	/**
	 * @desc автодекодирование строки.
	 * @param string $content
	 * @param string $key [optional]
	 * @return string Результат дешифрования.
	 */
	public static function autoDecode ($input, $key = null)
	{
		if (is_string ($input))
		{
			$p = strpos ($input, self::PROFILE_DELIMETER);
			if ($p)
			{
				$crypt = substr ($input, 0, $p);
				$input = substr ($input, $p + strlen (self::PROFILE_DELIMETER));
				return self::decode ($crypt, $input, $key);
			}
		}
		return $input;
	}

	/**
	 * @desc Дешифрование указанными методом.
	 * @param string $crypt Метод шифрования.
	 * @param string $content
	 * @param string $key [optional]
	 * @return string
	 */
	public static function decode ($crypt, $input, $key = null)
	{
		return self::get ($crypt)->decode ($input, $key);
	}

	/**
	 * @desc Шифрование указанным методом.
	 * @param string $crypt
	 * @param string $input
	 * @param string $key
	 * @return string
	 */
	public static function encode ($crypt, $input, $key = null)
	{
		return self::get ($crypt)->encode ($input, $key);
	}

	/**
	 * @desc Возвращает экземпляр класс, реализующего алгоритм шифрования.
	 * @param string $name Название алгоритма шифрования.
	 * @return Crypt_Abstract
	 */
	public static function get ($name)
	{
		if (!isset (self::$_crypts [$name]))
		{
			$crypt = 'Crypt_' . $name;
			self::$_crypts [$name] = new $crypt;
		}
		return self::$_crypts [$name];
	}

	/**
	 * @desc Проверяет соответсвует ли строка заданному значению
	 * @param string $input Строка.
	 * @param string $pattern Шаблон для сравнения.
	 * @param string $key [optional]
	 * @return boolean
	 */
	public static function isMatch ($input, $pattern, $key = null)
	{
		$p = strpos ($pattern, self::PROFILE_DELIMETER);
		if ($p)
		{
			$crypt = substr ($pattern, 0, $p);
			$pattern = substr ($pattern, $p + strlen (self::PROFILE_DELIMETER));
			return self::encode ($crypt, $input, $key) == $pattern;
		}

		return $input == $pattern;
	}

}

if (!class_exists ('Crypt_Abstract'))
{
	require dirname (__FILE__) . '/Abstract.php';
}