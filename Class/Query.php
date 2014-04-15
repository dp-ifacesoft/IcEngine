<?php

/**
 * Фабрика запросов
 *
 * @author morph, goorus
 * @Service("query", source={method="instance"})
 * @Service("queryBuilder", source={method="instance"})
 */
class Query
{
	const ASC				= 'ASC';
	const DELETE            = 'DELETE';
	const INSERT			= 'INSERT';
	const VALUES            = 'VALUES';
	const REPLACE			= 'REPLACE';
	const SHOW				= 'SHOW';
	const UPDATE            = 'UPDATE';
	const SET				= 'SET';
	const DESC				= 'DESC';
	const DISTINCT			= 'DISTINCT';
	const EXPLAIN			= 'EXPLAIN';
	const FROM 				= 'FROM';
	const GROUP				= 'GROUP';
	const HAVING			= 'HAVING';
	const INDEXES			= 'INDEXES';
	const INNER_JOIN		= 'INNER JOIN';
	const JOIN				= 'JOIN';
	const LEFT_JOIN			= 'LEFT JOIN';
	const RIGHT_JOIN		= 'RIGHT JOIN';
	const ORDER				= 'ORDER';
	const SELECT			= 'SELECT';
	const TABLE				= 'TABLE';
	const TYPE				= 'TYPE';
    const LIMIT             = 'LIMIT';
	const LIMIT_COUNT		= 'LIMITCOUNT';
	const LIMIT_OFFSET		= 'LIMITOFFSET';
	const VALUE				= 'VALUE';
	const WHERE				= 'WHERE';
	const SQL_AND			= 'AND';
	const SQL_OR            = 'OR';
	const USE_INDEX			= 'USE INDEX';
	const FORCE_INDEX		= 'FORCE INDEX';
	const CALC_FOUND_ROWS   = 'CALC_FOUND_ROWS';
	const TRUNCATE_TABLE    = 'TRUNCATE TABLE';
    const MERGE             = 'MERGE';
    const PUSH              = 'PUSH';
    const CREATE_TABLE		= 'CREATE TABLE';
	const IF_NOT_EXISTS		= 'IF NOT EXISTS';
	const ENGINE            = 'ENGINE';
	const DEFAULT_CHARSET	= 'DEFAULT CHARSET';
	const FIELD				= '__FIELD__';
	const COMMENT			= 'COMMENT';
    const ADD               = 'ADD';
	const ATTR              = '__ATTR__';
	const CHANGE            	= 'CHANGE';
	const DROP              = 'DROP';
	const INDEX             = '__INDEX__';
	const NAME              = '__NAME__';
	const ALTER_TABLE       = 'ALTER TABLE';
    const DROP_TABLE        = 'DROP TABLE';

	/**
	 * Уже созданные запросы
	 *
     * @var array
	 */
	protected $queries;

	/**
	 * @return Query_Abstract
	 */
	public function __call($method, $params)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $name = $serviceLocator->getService('helperService')
            ->normalizeName($method);
		$query = $this->factory($name);
		return call_user_func_array(array($query, $method), $params);
	}

	/**
	 * Создает и возвращает новый запрос.
	 *
     * Аналогично "new Query()".
	 * @return Query Новый запрос.
	 */
	public function instance()
	{
		return new self();
	}

	/**
	 * Создать запрос по типу
	 *
     * @param array $name
	 * @return Query_Abstract
	 */
	public function factory($name)
	{
		$className = 'Query_' . $name;
        $loader = IcEngine::serviceLocator()->getService('loader');
		if (!$loader->tryLoad($className)) {
			$className = 'Query_Select';
		}
		$query = new $className;
		return $query->reset();
	}
}