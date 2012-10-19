<?php
/**
 *
 * @desc Менеджер загрузчиков
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Bootstrap_Manager
{

	/**
	 * @desc Загрузчики
	 * @var array <Bootstrap_Abstract>
	 */
	protected static $_items;

	/**
	 * @desc Текущий загрузчик
	 * @var Bootstrab_Abstract
	 */
	protected static $_current;

	/**
	 * @desc Создает и возвращает загрузчик.
	 * @param string $name Название загрузчика.
	 * @param string $path [optional] Путь до загрузчика.
	 * @return Bootstrap_Abstract Экземпляр загрузчика.
	 */
	public static function get ($name, $path = null)
	{
		if (!isset (self::$_items [$name]))
		{
			$class = 'Bootstrap_' . $name;
			self::$_items [$name] = new $class ($path);
		}

		if (!self::$_current)
		{
			self::$_current = self::$_items [$name];
		}

		return self::$_items [$name];
	}

	/**
	 * @desc Возвращает текущий загрузчик.
	 * @return Bootstrap_Abstract
	 */
	public static function current ()
	{
		return self::$_current;
	}

}