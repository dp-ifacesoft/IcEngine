<?php
/**
 *
 * @desc Абстрактный класс менеджера
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
abstract class Manager_Abstract
{
	/**
	 * @var array
	 */
	protected static $_config;

	/**
	 * @desc Конфиги менеджера
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (static::$_config))
		{
			static::$_config = Config_Manager::get (
				get_called_class (),
				static::$_config
			);
		}
		return static::$_config;
	}

}