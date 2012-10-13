<?php

/**
 * @desc Регистр глобальных переменных
 * @package IcEngine
 * @author Юрий Шведов
 * @copyright i-complex.ru
 */
class Registry
{
	/**
	 * @desc Хранимые данные
	 * @var array
	 */
	public static $data = array ();

	/**
	 * @desc Возвращает true, если значение задано и отлично от null.
	 * Иначе false.
	 * @param string $index
	 * @return boolean
	 */
	public static function defined ($index)
	{
		return isset (self::$data [$index]);
	}

	/**
	 * @desc Чтение значения
	 * @param string $index
	 * @return mixed
	 */
	public static function get ($index)
	{
		return self::$data [$index];
	}

	/**
	 * @desc Запись значения
	 * @param string $index
	 * @param mixed $value
	 */
	public static function set ($index, $value)
	{
		self::$data [$index] = $value;
	}

	/**
	 * @desc silent get. Не вызывает ошибки, если значение не определено.
	 * @param string $index
	 * @return mixed|null
	 */
	public static function sget ($index)
	{
		return isset (self::$data [$index]) ? self::$data [$index] : null;
	}

	/**
	 * @desc Получение по ссылке
	 * @param string $index
	 * @return $mixed
	 */
	public static function &rget ($index)
	{
		return self::$data [$index];
	}

	/**
	 * @desc Передача по ссылке
	 * @param string $index
	 * @param mixed $value
	 */
	public static function rset ($index, &$value)
	{
		self::$data [$index] = &$value;
	}
	
}