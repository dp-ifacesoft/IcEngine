<?php

/**
 *
 * @desc Транслятор в SQL запрос
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Query_Translator_Mysql_Select extends Query_Translator_Abstract
{
	// Для построения SQL запроса
	const SQL_AND			= 'AND';
	const SQL_ASC			= 'ASC';
	const SQL_COMMA			= ',';
	const SQL_DELETE		= 'DELETE';
	const SQL_DESC			= 'DESC';
	const SQL_DISTINCT		= 'DISTINCT';
	const SQL_EXPLAIN		= 'EXPLAIN';
	const SQL_DOT			= '.';
	const SQL_EQUAL			= '=';
	const SQL_ESCAPE		= '`';
	const SQL_FROM			= 'FROM';
	const SQL_GROUP_BY		= 'GROUP BY';
	const SQL_HAVING		= 'HAVING';
	const SQL_IN			= 'IN';
	const SQL_INSERT		= 'INSERT';
	const SQL_INNER_JOIN	= 'INNER JOIN';
	const SQL_LEFT_JOIN		= 'LEFT JOIN';
	const SQL_RIGHT_JOIN	= 'RIGHT JOIN';
	const SQL_LIMIT			= 'LIMIT';
	const SQL_LIKE			= 'LIKE';
	const SQL_ON			= 'ON';
	const SQL_ORDER_BY		= 'ORDER BY';
	const SQL_QUOTE			= '"';
	const SQL_REPLACE		= 'REPLACE';
	const SQL_SELECT		= 'SELECT';
	const SQL_SET			= 'SET';
	const SQL_SHOW			= 'SHOW';
	const SQL_RLIKE			= 'RLIKE';
	const SQL_UPDATE		= 'UPDATE';
	const SQL_VALUES		= 'VALUES';
	const SQL_WHERE			= 'WHERE';
	const SQL_WILDCARD		= '*';
	const SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';

	const WHERE_VALUE_CHAR	= '?';

	/**
	 * @see Helper_Mysql::escape()
	 */
	public function _escape ($value)
	{
		if (strpos($value, '(') === false) {
			return Helper_Mysql::escape ($value);
		} else {
			return $value;
		}
	}

	/**
	 * @see Helper_Mysql::quote()
	 */
	public function _quote ($value)
	{
		return Helper_Mysql::quote ($value);
	}

	/**
	 * @desc Рендерит часть SQL CALC FOUND ROWS
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _partCalcFoundRows (Query_Abstract $query)
	{
		if (!$query->part (Query::CALC_FOUND_ROWS))
		{
			return '';
		}

		return self::SQL_CALC_FOUND_ROWS;
	}

	protected function _partExplain (Query_Abstract $query)
	{
		return $query->part (Query::EXPLAIN) ? self::SQL_EXPLAIN : '';
	}

	/**
	 * @desc Рендерит часть distinct
	 * @param Query_Abstract $query
	 * @return string
	 */
	protected function _partDistinct (Query_Abstract $query)
	{
		return $query->part (Query::DISTINCT) ? self::SQL_DISTINCT : '';
	}

	/**
	 * @desc Рендерит часть запроса from
	 * @param Query_Abstract $query
	 * @param type $use_alias
	 * @return string
	 */
	public function _renderFrom (Query_Abstract $query, $use_alias = true)
	{
		$sql = self::SQL_FROM;
		$i = 0;

		$from = $query->part (Query::FROM);

		if (!$from)
		{
			return;
		}

		if (count ($from) > 1)
		{
			foreach ($from as $a=>$v)
			{
				if ($v [Query::JOIN] == Query::FROM)
				{
					unset ($from [$a]);
					$from = array_merge (array ($a=>$v), $from);
					break;
				}
			}
		}

		$froms = $from;

		foreach ($froms as $alias => $from)
		{
			if ($from [Query::TABLE] instanceof Query_Select)
			{
				$table = '(' .
					$this->_renderSelect ($from [Query_Select::TABLE]) .
					')';
			}
			else
			{
				$table =
					strpos ($from [Query::TABLE], self::SQL_ESCAPE) !== false
					? $from [Query::TABLE]
					: Model_Scheme::table ($from [Query::TABLE]);

				$table = $this->_escape ($table);
			}

			$alias = $this->_escape ($alias);

			if ($from [Query::JOIN] == Query::FROM)
			{
				$a = ($table == $alias || !$use_alias);
				$sql .=
					($i ? self::SQL_COMMA : ' ') .
					($a ? $table : ($table . ' AS ' . $alias));
			}
			else
			{
				if (is_array ($from [Query::WHERE]))
				{
					$where =
						$this->_escape ($from [Query::WHERE][0]) .
						self::SQL_DOT .
						$this->_escape ($from [Query::WHERE][1]) .
						'=' .
						$this->_escape ($from [Query::WHERE][2]) .
						self::SQL_DOT .
						$this->_escape ($from [Query::WHERE][3]);
				}
				else
				{
					$where = $from [Query::WHERE];
				}
				$sql .= ' ' .
					$from [Query::JOIN] . ' ' .
					$table . ' AS ' . $alias . ' ' .
					self::SQL_ON .
					'(' . $from [Query::WHERE] . ')';
			}
			$i++;
		}
		return $sql .
			self::_renderUseIndex ($query) .
			self::_renderForceIndex ($query);
	}

	/**
	 * @desc Рендерит часть запроса FORCE INDEX
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderForceIndex (Query_Abstract $query)
	{
		return self::_renderUseIndex ($query);
	}

	/**
	 * @desc Рендерит часть запроса group
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderGroup (Query_Abstract $query)
	{
		$groups = $query->part (Query::GROUP);

		if (empty ($groups))
		{
			return '';
		}

		$columns = array ();
		foreach ($groups as $column)
		{
			if (
				strpos ($column, '(') !== false ||
				strpos ($column, '`') !== false
			)
			{
				$columns [] = $column;
			}
			elseif (strpos ($column, self::SQL_DOT) !== false)
			{
				$column = explode (self::SQL_DOT, $column);
				$column = array_map (array($this, '_escape'), $column);
				$columns [] = implode (self::SQL_DOT, $column);
			}
			else
			{
				$columns [] = $this->_escape ($column);
			}
		}
		return
			self::SQL_GROUP_BY . ' ' .
			implode (self::SQL_COMMA, $columns);
	}

	public function _renderHaving (Query_Abstract $query)
	{
		$having = $query->part (Query::HAVING);

		if (empty ($having))
		{
			return '';
		}
		return
		self::SQL_HAVING . ' ' . $having;
	}

	/**
	 * @desc Рендерит mysql терм если он массив
	 * @param array $value
	 * @return string
	 */
	public function _renderInArray (array $value)
	{
		if (empty ($value))
		{
			return 'NULL';
		}

		$result = implode (',', array_map (array ($this, '_quote'), $value));

		return $result;
	}

	/**
	 * @desc Рендер части запроса limit
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderLimitoffset (Query_Abstract $query)
	{
		$sql = '';
		$limit_count = $query->part (Query::LIMIT_COUNT);

		if (!empty ($limit_count))
		{
			$sql .=
				' LIMIT ' . (int) $query->part (Query::LIMIT_OFFSET) .
				self::SQL_COMMA . (int) $query->part (Query::LIMIT_COUNT);
		}
		elseif ($query->part (Query::LIMIT_OFFSET))
		{
			$sql .= ' LIMIT ' . (int) $query->part (Query::LIMIT_OFFSET);
		}

		return $sql;
	}

	/**
	 * @desc Рендер части запроса order
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderOrder (Query_Abstract $query)
	{
		$orders = $query->part (Query::ORDER);
		if (!$orders)
		{
			return '';
		}

		$columns = array ();
		foreach ($orders as $order)
		{
			$field = $order[0];
			if (strpos($field, '(') === false) {
				$field = explode (self::SQL_DOT, $order [0]);
				$field = array_map (array($this, '_escape'), $field);
				$field = implode (self::SQL_DOT, $field);
			}
			if ($order [1] == self::SQL_DESC)
			{
				$columns [] = $field . ' ' . self::SQL_DESC;
			}
			else
			{
				$columns [] = $field;
			}
		}
		return 'ORDER BY ' . implode (self::SQL_COMMA, $columns);
	}

	/**
	 * @desc Рендеринг SELECT запроса.
	 * @param Query_Abstract $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function _renderSelect (Query_Abstract $query)
	{
		$sql = '';

		$parts = $query->parts ();

		if ($parts [Query::SELECT])
		{
			$sql =
				self::_partExplain ($query) . ' ' .
				'SELECT ' .
				self::_partCalcFoundRows ($query) . ' ' .
				self::_partDistinct ($query) . ' ';

			$columns = array ();
			foreach ($parts [Query::SELECT] as $alias => $sparts)
			{
				if (is_array ($sparts))
				{
					if (count ($sparts) > 1)
					{
						if (empty ($sparts [0]))
						{
							$source = '';
						}
						else
						{
							$source =
								$this->_escape ($sparts [0]) .
								self::SQL_DOT;
						}

						if (
							strpos ($sparts [1], self::SQL_WILDCARD) !== false ||
							strpos ($sparts [1], '(') === false ||
							strpos ($sparts [1], ' ') === false ||
							strpos ($sparts [1], '.') === false ||
							strpos ($sparts [1], '`') === false
						)
						{
							$source .= $sparts [1];
						}
						else
						{
							$source .= $this->_escape ($sparts [1]);
						}
					}
					elseif (strpos ($sparts [0], self::SQL_WILDCARD) !== false)
					{
						$source = $sparts [0];
					}
					else
					{
						$source = $this->_escape ($sparts [0]);
					}
				}
				elseif (
					strpos ($sparts, self::SQL_WILDCARD) === false &&
					strpos ($sparts, '(') === false &&
					strpos ($sparts, ' ') === false &&
					strpos ($sparts, '`') === false &&
					strpos ($sparts, '.') === false
				)
				{
					$source = $this->_escape ($sparts);
				}
				elseif (strpos ($sparts, self::SQL_WILDCARD) !== false)
				{
					$source = explode ('.', $sparts);
					$source [0] = $this->_escape ($source [0]);
					$source = implode ('.', $source);
				}
				else
				{
					$source = $sparts;
				}

				if (is_numeric ($alias))
				{
					$columns [] = $source;
				}
				elseif (
					strpos ($alias, self::SQL_WILDCARD) !== false ||
					strpos ($alias, '(') !== false ||
					strpos ($alias, ' ') !== false ||
					strpos ($alias, '.') !== false
				)
				{
					$columns [] = $source;
				}
				else
				{
					$columns [] = $source . ' AS ' . $this->_escape ($alias);
				}
			}
		}

		return (!empty ($columns) ? $sql . implode (self::SQL_COMMA, $columns) : '') . ' ' .
			self::_renderFrom ($query) . ' ' .
			self::_renderUseIndex ($query) . ' ' .
			self::_renderWhere ($query) . ' ' .
			self::_renderGroup ($query) . ' ' .
			self::_renderHaving ($query) . ' ' .
			self::_renderOrder ($query) . ' ' .
			self::_renderLimitoffset ($query);
	}

	/**
	 * @desc Рендерит часть запроса USE INDEX
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderUseIndex (Query_Abstract $query)
	{
		$indexes = $query->part (Query::INDEX);
		if (!$indexes)
		{
			return '';
		}

		return $indexes [1].'(' . implode (',', (array) $indexes [0]) . ')';
	}

	/**
	 * @desc Рендер части запроса Where
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderWhere (Query_Abstract $query)
	{
		$wheres = $query->part (Query::WHERE);

		if (!$wheres)
		{
			return '';
		}

		$sql = 'WHERE ';


		foreach ($wheres as $i => $where)
		{
			if (isset ($where ['empty']))
			{
				$sql = '';
			}
		}

		foreach ($wheres as $i => $where)
		{
			if ($i > 0)
			{
				$sql .= ' ' . $where [0] . ' ';

			}

			if (array_key_exists (Query::VALUE, $where))
			{
				if ($where [Query::VALUE] instanceof Query_Select)
				{
					$where [Query::VALUE] = '(' . $this->_renderSelect (
						$where [Query::VALUE]
					) . ')';
				}

				$sql .= $this->_quoteCondition (
					$where [Query::WHERE],
					$where [Query::VALUE]
				);
			}
			else
			{
				if ($where [Query::WHERE] instanceof Query_Select)
				{
					$w = $where [Query::WHERE]->getPart (Query::WHERE);
					$w [0]['empty'] = true;
					$where [Query::WHERE]->setPart (Query::WHERE, $w);
					$sql .= '(' .
						$this->_renderSelect ($where [Query::WHERE]) . ')';
				}
				else
				{
					 $sql .= $this->_quoteCondition (
						$where [Query::WHERE]
					);
				}
			}
		}

		return $sql;
	}

	/**
	 * @desc Экранирование условий запроса
	 * @param string $condition
	 * @param mixed $value [optional]
	 * @return string
	 */
	protected function _quoteCondition ($condition)
	{
		if (func_num_args () == 1)
		{
			return $condition;
		}

		$value = func_get_arg (1);

		if (strpos ($condition, self::WHERE_VALUE_CHAR) === false)
		{
			if (is_array ($value))
			{
				$value = ' IN (' . $this->_renderInArray ($value) . ')';
			}
			else
			{
				$value = '=' . $this->_quote ($value);
			}

			if (
				strpos ($condition, '(') === false &&
				strpos ($condition, ' ') === false &&
				strpos ($condition, '.') === false &&
				strpos ($condition, '`') === false
			)
			{
				return $this->_escape ($condition) . $value;
			}

			return $condition . $value;
		}
		else
		{
			$char_pos = 0;
			$i = 0;

			if (is_array ($value))
			{
				foreach ($value as $key => $val)
				{
					if (!is_numeric ($key))
					{
						$condition = str_replace (
							':' . $key,
							is_array ($val)
								? $this->_renderInArray ($val)
								: $this->_quote ($val),
							$condition
						);
					}
				}
			}

			$value = (array) $value;
			$i = 0;

			while ($char_pos !== false)
			{
				$char_pos = strpos ($condition, self::WHERE_VALUE_CHAR, $char_pos);
				if ($char_pos === false)
				{
					break;
				}
				if (!array_key_exists ($i, $value))
				{
					break;
				}
				$val = $value [$i];
				$val = is_array ($val)
					? $this->_renderInArray ($val)
					: $this->_quote ($val);
				$left = substr ($condition, 0, $char_pos);
				$right = substr ($condition, $char_pos + 1);
				$condition = $left . $val . $right;
				$char_pos += strlen ($val);
				$i++;
			}

			return $condition;
		}
	}
}