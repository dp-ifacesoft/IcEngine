<?php

/**
 * Менеджер запросов UOW, решает какого типа запрос
 *
 * @author neon
 * @Service("unitOfWorkManager")
 */
class Unit_Of_Work_Manager
{
	/**
	 * Получить тип запроса UOW
	 */
	public static function get(Query_Abstract $query)
	{
		$className = 'Unit_Of_Work_' . get_class($query);
		return new $className();
	}

	public static function byName($name)
	{
		$className = 'Unit_Of_Work_Query_' . ucfirst(strtolower($name));
		return new $className();
	}
}