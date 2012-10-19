<?php
/**
 *
 * @desc Менеджер фильтров
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Manager
{

	/**
	 * @desc Подключенные фильтры.
	 * @var array <Filter_Abstract>
	 */
	protected static $_filters = array ();

	/**
	 * @param string $name Фильтр.
	 * @return Filter_Abstract
	 */
	public static function get ($name)
	{
		if (isset (self::$_filters [$name]))
		{
			return self::$_filters [$name];
		}

		$class = 'Filter_' . $name;
		return self::$_filters [$name] = new $class ();
	}

	/**
	 * @desc Фильтрация
	 * @param string $name Фильтр
	 * @param mixed $data
	 * @return mixed
	 */
	public static function filter ($name, $data)
	{
		return self::get ($name)->filter ($data);
	}

	/**
	 * @desc Фильтрация с использованием схемы
	 * @param string $name Фильтр
	 * @param string $field
	 * @param stdClass $data
	 * @param stdClass|Objective $scheme
	 * @return mixed
	 */
	public static function filterEx ($name, $field, $data, $scheme)
	{
		return self::get ($name)->filterEx ($field, $data, $scheme);
	}

}