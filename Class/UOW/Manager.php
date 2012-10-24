<?php

/**
 * Менеджер запросов UOW, решает какого типа запрос
 *
 * @author neon
 */
class UOW_Manager
{
	/**
	 * Получить тип запроса UOW
	 */
	public static function get(Query_Abstract $query)
	{
		$className = 'UOW_' . get_class($query);
		return new $className();
	}

	public static function byName($name)
	{
		$className = 'UOW_Query_' . ucfirst(strtolower($name));
		return new $className();
	}
}