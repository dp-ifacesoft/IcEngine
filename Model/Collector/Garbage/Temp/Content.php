<?php

/**
 * @desc Gc для удаление просроченного темп контента
 * @author Илья Колесников
 * @packager IcEngine
 * @copyright i-complex.ru
 */
class Collector_Garbage_Temp_Content extends Collector_Garbage_Abstract
{
	protected static $_config = array (
		// Дельта дней просрочки
		'max_days'	=> 2
	);

	public function process ()
	{
		$era_day = Helper_Date::eraDayNum ();
		$max_days = max (2, (int) self::config ()->max_days);

		$query = Query::instance ()
			->delete ()
			->from ('Temp_Content')
			->where ('day<=?', $era_day - $max_days);

		DDS::execute ($query);
	}
}