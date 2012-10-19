<?php

/**
 *
 * @package IcEngine
 *
 */
abstract class Collector_Garbage_Manager
{
	/**
	 * @desc Контейнер схемы по умолчанию
	 * @var string
	 */
	const CONTAINER = 'Collector_Garbage_Scheme';

	/**
	 * @desc Коллекторы
	 * @var array
	 */
	private static $_collectors = array ();

	/**
	 * @desc Получить список коллекторов
	 * @return array <Collector_Garbage_Scheme>
	 */
	public static function getCollectors ()
	{
		if (!self::$_collectors)
		{
			$collection = new Collector_Garbage_Scheme_Collection ();
			self::$_collectors = $collection
				->addOptions (array (
					'name'	=> 'Active'
				))
				->items ();
		}
		return self::$_collectors;
	}

	/**
	 * @desc Запустить менеджер коллекторов
	 * @return boolean
	 */
	public static function process ()
	{
		if (!self::$_collectors)
		{
			return true;
		}

		foreach (self::$_collectors as $collector)
		{
			$collector = Model_Manager::get (
				self::CONTAINER,
				$collector->id
			);
			if ($collector)
			{
				$config = Config_Manager::get (
					self::CONTAINER,
					$collector->name
				);

				if ($config)
				{
					$config = $config->config ();
				}

				$collector->process ($config);
			}
		}

		return true;
	}
}