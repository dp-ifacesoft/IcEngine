<?php

/**
 * @desc Фабрика запросов
 * @author morph, goorus
 * @method Query_Select select() select(array|string $colums) Добавить в запрос SELECT часть
 * @method Query_Insert insert() insert(string $table) Запрос преобразуется в запрос на вставку
 * @method Query_Update update() update(string $table) Преобразует запрос к запросу на обновление
 * @method Query_Delete delete() delete(void) Это запрос на удаление
 * @method Query_Replace replace() replace(string $table) Запрос преобразуется в запрос на replace
 * @method Query_Show show() show(array|string $columns) Часть запроса show
 * @method Query_Alter_Table alterTable() alterTable(string $table) Часть запроса ALTER
 * @method Query_Create_Table createTable() createTable(string $table) Часть запроса CREATE TABLE
 * @method Query_Drop_Table dropTable() dropTable(string $table) Часть запроса DROP TABLE
 * @method Query_Truncate_Table truncateTable() truncateTable(string $table) Часть запроса TRUNCATE TABLE
 */
class Query
{
	const ASC				= 'ASC';
	const DELETE			= 'DELETE';
	const INSERT			= 'INSERT';
	const VALUES			= 'VALUES';
	const REPLACE			= 'REPLACE';
	const SHOW				= 'SHOW';
	const UPDATE			= 'UPDATE';
	const SET				= 'SET';
	const DESC				= 'DESC';
	const DISTINCT			= 'DISTINCT';
	const EXPLAIN			= 'EXPLAIN';
	const FROM 				= 'FROM';
	const GROUP				= 'GROUP';
	const HAVING			= 'HAVING';
	const INDEX				= 'INDEX';
	const INDEXES			= 'INDEXES';
	const INNER_JOIN		= 'INNER JOIN';
	const JOIN				= 'JOIN';
	const LEFT_JOIN			= 'LEFT JOIN';
	const RIGHT_JOIN		= 'RIGHT JOIN';
	const ORDER				= 'ORDER';
	const SELECT			= 'SELECT';
	const TABLE				= 'TABLE';
	const TYPE				= 'TYPE';
	const LIMIT_COUNT		= 'LIMITCOUNT';
	const LIMIT_OFFSET		= 'LIMITOFFSET';
	const VALUE				= 'VALUE';
	const WHERE				= 'WHERE';
	const SQL_AND			= 'AND';
	const SQL_OR			= 'OR';
	const USE_INDEX			= 'USE INDEX';
	const FORCE_INDEX		= 'FORCE INDEX';
	const CALC_FOUND_ROWS   = 'CALC_FOUND_ROWS';

	/**
	 * @desc Уже созданные запросы
	 * @var array
	 */
	protected static $_queries;

	/**
	 * @return Query_Abstract
	 */
	public function __call ($method, $params)
	{
		$name = $this->normalizaName ($method);
		$query = self::factory ($name);
		return call_user_func_array (
			array ($query, $method),
			$params
		);
	}

	/**
	 * @desc Создает и возвращает новый запрос.
	 * Аналогично "new Query()".
	 * @return Query Новый запрос.
	 */
	public static function instance ()
	{
		return new self ();
	}

	/**
	 * @desc Создать запрос по типу
	 * @param array $name
	 * @return Query_Abstract
	 */
	public static function factory ($name)
	{
		$class_name = 'Query_' . $name;
		if (!Loader::tryLoad ($class_name))
		{
			$class_name = 'Query_Select';
		}
		$query = new $class_name;
		return $query->reset ();
	}

	/**
	 * @desc Привести имя метод из вида methodName к виду Method_Name
	 * @param string $name
	 */
	public static function normalizaName ($name)
	{
		$matches = array ();
		$reg_exp = '#([A-Z]*[a-z]+)#';
		preg_match_all ($reg_exp, $name, $matches);
		if (empty ($matches [1][0]))
		{
			return $name;
		}
		return implode ('_', array_map ('ucfirst', $matches [1]));
	}
}