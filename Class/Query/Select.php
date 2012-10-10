<?php
/**
 * @desc Запрос к источнику данных.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Query_Select extends Query_Abstract
{
	/**
	 * @see Query_Abstract::$_defaults
	 */
	public static $_defaults = array (
		Query::CALC_FOUND_ROWS	=> false,
		Query::DISTINCT			=> false,
		Query::EXPLAIN			=> false,
		Query::JOIN				=> false,
		Query::FROM				=> array(),
		Query::SELECT			=> array(),
		Query::SHOW				=> array(),
		Query::WHERE			=> array(),
		Query::GROUP			=> array(),
		Query::HAVING			=> null,
		Query::ORDER			=> array(),
		Query::LIMIT_COUNT		=> null,
		Query::LIMIT_OFFSET		=> 0,
		Query::INDEX			=> array()
	);

	/**
	 * @desc Добавление джойна таблицы к запросу
	 * @param string|array $table Название таблицы или
	 * пара (table => alias) или, в случае нескольких алиасов
	 * (table => array (alias1, alias2,...)).
	 * Джойн нескольких таблиц не поддерживается.
	 * @param string $type
	 * @param string $condition optional
	 */
	protected function _join ($table, $type, $condition = null)
	{
		if (is_array ($table))
		{
			reset ($table);
			$aliases = (array) current ($table);
			$table = key ($table);
		}
		else
		{
			$aliases = (array) $table;
		}

		foreach ($aliases as $alias)
		{
			$this->_parts [Query::FROM] [$alias] = array (
				Query::TABLE		=> $table,
				Query::WHERE		=> $condition,
				Query::JOIN		=> $type
			);
		}
	}

	/**
	 * @desc В запрос будет добавлен аргумент для получения полного
	 * количества строк (SQL_CALC_FOUND_ROWS).
	 * Работает только для Mysql.
	 * @return Query Этот объект.
	 */
	public function calcFoundRows ()
	{
	   $this->_parts [Query::CALC_FOUND_ROWS] = true;
	   return $this;
	}

	/**
	 * @desc Устанавливает значение флага DISTINCT
	 * @param boolean $value
	 * @return Query
	 */
	public function distinct ($value)
	{
		$this->_parts [Query::DISTINCT] = (bool) $value;
		return $this;
	}
	
	/**
	 * @desc Устанавливает значение флага EXPLAIN
	 * @param boolean $value
	 * @return Query
	 */
	public function explain ($value)
	{
		$this->_parts [Query::EXPLAIN] = (bool) $value;
		return $this;
	}
	
	
	public function having ($condition)
	{
		$this->_parts [Query::HAVING] = $condition;
		return $this;
	}
	
	/**
	 * @desc Часть запроса from
	 * @param string|array $table Имя таблицы или array('table' => 'alias')
	 * @param string $alias
	 * @return Query
	 */
	public function from ($table, $alias = null)
	{
		$this->_join (
			$alias ? array ($table => $alias) : $table,
			Query::FROM
		);
		return $this;
	}

	/**
	 * @desc Использовать индекс
	 * @param string $index Название индекса
	 * @return Query Этот запрос.
	 */
	public function forceIndex ($index)
	{
		$this->_parts [Query::INDEX] = array (
			$index,
			Query::FORCE_INDEX
		);
		return $this;
	}

	/**
	 * @see Query_Abstract::getTags()
	 */
	public function getTags ()
	{
		$tags = array ();

		$from = $this->getPart (Query::FROM);
		if (!$from)
		{
			return;
		}
		foreach ($from as $info)
		{
			$tags [] = Model_Scheme::table ($info [Query::TABLE]);
		}
		return array_unique ($tags);
	}

	/**
	 * @desc Устанавливает правило группировки
	 * @param array|string $fields
	 * @param string $_ Поля для группировки.
	 * @return Query
	 */
	public function group ($columns)
	{
		if (!is_array ($columns))
		{
			$columns = func_get_args ();
		}

		foreach ($columns as $column)
		{
			$this->_parts [Query::GROUP] [] = $column;
		}

		return $this;
	}

	/**
	 * @desc Часть запроса Inner Join
	 * @param string|array $table
	 * @param string $condition
	 * @return Query
	 */
	public function innerJoin ($table, $condition)
	{
		$this->_join ($table, Query::INNER_JOIN, $condition);

		return $this;
	}

	/**
	 * @desc Часть запроса leftJoin
	 * @param string|array $table
	 * @param string $condition
	 * @return Query
	 */
	public function leftJoin ($table, $condition)
	{
		$this->_join ($table, Query::LEFT_JOIN, $condition);

		return $this;
	}

	/**
	 * 
	 * @param string|array $table
	 * @param string $condition
	 * @return Query
	 */
	public function rightJoin ($table, $condition)
	{
		$this->_join ($table, Query::RIGHT_JOIN, $condition);
		
		return $this;
	}
	
	/**
	 * @desc Задает правило сортировки
	 * @param string|array $sort
	 * 		'id' | array('id' => Query::DESC)
	 * @return Query
	 */
	public function order ($sort)
	{
		if (!is_array ($sort))
		{
			$sort = func_get_args ($sort);
		}

		foreach ($sort as $field => $direction)
		{
			if (is_numeric ($field))
			{
				$field = $direction;
				$direction = Query::ASC;
			}
			$this->_parts [Query::ORDER] [] = array ($field, $direction);
		}

		return $this;
	}

	/**
	 * @desc Добавляет к запросу условие "или".
	 * @param string $condition
	 * @param mixed $value
	 * @return Query
	 * @deprecated
	 */
	public function orWhere ($condition)
	{
		$where = array (
			0				=> Query::SQL_OR,
			Query::WHERE		=> $condition
		);

		if (func_num_args () > 1)
		{
			$where [Query::VALUE] = func_get_arg (1);
		}

		$this->_parts [Query::WHERE] [] = $where;

		return $this;
	}

	/**
	 * @see Query_Abstract::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::SELECT;
		return $this;
	}

	/**
	 * @desc Добавить в запрос SELECT часть.
	 * @param string|array $columns
	 * @tutorial
	 * 		select (
	 * 			'table1'	=> array ('field11' => 'alias11', 'field12'),
	 * 			'table2'	=> array ('field21', 'field22')
	 * 		)
	 * 		select ('field1', 'field2')
	 * 		select ('COUNT(*)')
	 * 		select (
	 * @return Query
	 */
	public function select ($columns)
	{
		foreach (func_get_args () as $columns)
		{
			if (is_array ($columns))
			{
				// Передано название таблицы
				foreach ($columns as $table => $fields)
				{
					$fields = (array) $fields;
					foreach ($fields as $name => $aliases)
					{
						$aliases = (array) $aliases;
						foreach ($aliases as $alias)
						{
							// реальное название столбца
							$rname = is_numeric ($name) ? $alias : $name;

							$this->_parts [Query::SELECT] [$alias] =
								array ($table, $rname);
						}
					}
				}
			}
			elseif ($columns)
			{
				// переданы только поля
				$args = func_get_args ();
				for ($i = 0, $count = count ($args); $i < $count; $i++)
				{
					$this->_parts [Query::SELECT] [$args [$i]] = $args [$i];
				}
			}
		}

		return $this;
	}

	/**
	 * @desc Добавление джойна таблицы, если она еще не подключна.
	 * @param string $table Название таблицы. Алиас не принимается.
	 * @param string $condition
	 * @return Query
	 */
	public function singleInnerJoin ($table, $condition)
	{
		$joins = $this->getPart (Query::FROM);
		foreach ($joins as $data)
		{
			if ($data [Query::TABLE] == $table)
			{
				// Таблица уже подключена
				if ($data [Query::JOIN] == Query::FROM)
				{
					return $this->where ($condition);
				}

				if ($data [Query::JOIN] == Query::LEFT_JOIN)
				{
					$data [Query::JOIN] = Query::INNER_JOIN;
				}

				if ($data [Query::WHERE] != $condition)
				{
					$data [Query::WHERE] =
						'(' .
							$data [Query::WHERE] .
						') AND (' .
							$condition .
						')';
				}
				return $this;
			}
		}

		// Еще не подключена
		return $this->innerJoin ($table, $condition);
	}

	/**
	 * @desc Добавление джойна таблицы, если она еще не подключна.
	 * @param string $table Название таблицы.
	 * Алиас не принимается.
	 * @param string $condition
	 * @return Query
	 */
	public function singleLeftJoin ($table, $condition)
	{
		$joins = $this->getPart (Query::FROM);
		foreach ($joins as $data)
		{
			if ($data [Query::TABLE] == $table)
			{
				// Таблица уже подключена
				if ($data [Query::JOIN] == Query::FROM)
				{
					return $this;
				}

				if ($data [Query::WHERE] != $condition)
				{
					$data [Query::WHERE] =
						'(' .
							$data [Query::WHERE] .
						') AND (' .
							$condition .
						')';
				}
				return $this;
			}
		}

		// Еще не подключена
		return $this->leftJoin ($table, $condition);
	}

	/**
	 * @desc Формирует новый запрос, определяющий количество записей,
	 * которые будут выбраны в результате этого запроса.
	 * @return Query Новый запрос
	 */
	public function toCountQuery ()
	{
		$count_query = clone $this;

		$count_query
			->resetPart (Query::SELECT)
			->select ('COUNT(1) AS `count`')
			->resetPart (Query::LIMIT_COUNT)
			->resetPart (Query::LIMIT_OFFSET)
			->resetPart (Query::ORDER);

		return $count_query;
	}

	/**
	 * @desc Часть запроса limit
	 * @param integer $count OPTIONAL The number of rows to return.
	 * @param integer $offset OPTIONAL Start returning after this many rows.
	 * @return Query
	 */
	public function limit ($count = null, $offset = null)
	{
		$this->_parts [Query::LIMIT_COUNT]  = (int) $count;
		$this->_parts [Query::LIMIT_OFFSET] = (int) $offset;
		return $this;
	}

	/**
	 * @desc Использовать индекс
	 * @param string $index Название индекса
	 * @return Query Этот запрос.
	 */
	public function useIndex ($index)
	{
		$this->_parts [Query::INDEX] = array (
			$index,
			Query::USE_INDEX
		);
		return $this;
	}

	/**
	 * @desc Добавляет условие к запросу
	 * @param string $condition Условие
	 * @param string $value [optional] Значение, подставляемое в условие.
	 * @return Query
	 */
	public function where ($condition)
	{
		$where = array (
			0				=> Query::SQL_AND,
			Query::WHERE		=> $condition
		);

		if (func_num_args () > 1)
		{
			$where [Query::VALUE] = func_get_arg (1);
		}

		$this->_parts [Query::WHERE] [] = $where;

		return $this;
	}
}