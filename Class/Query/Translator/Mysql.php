<?php
/**
 *
 * @desc Транслятор в SQL запрос
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Query_Translator_Mysql extends Query_Translator
{

	// Для построения SQL запроса
	const SQL_AND			= 'AND';
	const SQL_ASC			= 'ASC';
	const SQL_COMMA			= ',';
	const SQL_DELETE		= 'DELETE';
	const SQL_DESC			= 'DESC';
	const SQL_DISTINCT		= 'DISTINCT';
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

	const WHERE_VALUE_CHAR	= '?';

	/**
	 * @desc Обособляет название mysql терма, если в этом есть необходимость.
	 * Функция вернет исходную строку, если в ней присутствуют спец. символы
	 * (точки, скобки, кавычки, знаки мат. операций и т.п.)
	 * @param string $value Название терма.
	 * @return string Резултат обособления.
	 */
	public function _escape ($value)
	{
		if (
			strpos ($value, self::SQL_WILDCARD) === false &&
			strpos ($value, '(') === false &&
			strpos ($value, ' ') === false &&
			strpos ($value, '.') === false &&
			strpos ($value, '<') === false &&
			strpos ($value, '>') === false &&
			strpos ($value, '`') === false
		)
		{
			//return self::SQL_ESCAPE . mysql_real_escape_string ($value) . self::SQL_ESCAPE;
			return self::SQL_ESCAPE . addslashes (iconv ('UTF-8', 'UTF-8//IGNORE', $value)) . self::SQL_ESCAPE;
		}
		return $value;
	}

	/**
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function _quote ($value)
	{
		if (is_array ($value))
		{
			debug_print_backtrace ();
			die ();
		}
//		if (is_array ($value)) debug_print_backtrace();
//		return self::SQL_QUOTE . mysql_real_escape_string ($value) . self::SQL_QUOTE;
		return self::SQL_QUOTE . addslashes (iconv ('UTF-8', 'UTF-8//IGNORE', $value)) . self::SQL_QUOTE;
	}

	public function _partCalcFoundRows (Query $query)
	{
		if (!$query->part (Query::CALC_FOUND_ROWS))
		{
			return '';
		}

		return 'SQL_CALC_FOUND_ROWS';
	}

	/**
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function _partDistinct (Query $query)
	{
		return $query->part (Query::DISTINCT) ? self::SQL_DISTINCT : '';
	}

	public function _renderDelete (Query $query)
	{
		$parts = $query->parts ();
		//$parts = implode(', ', $parts[Query::DELETE]);
		foreach($parts[Query::DELETE] as $key => $part)
		{
			$parts[Query::DELETE][$key] = strpos ($part, self::SQL_ESCAPE) !== false ?
				$part :
				strtolower (Model_Scheme::table ($part));
			$parts[Query::DELETE][$key] = $this->_escape ($parts[Query::DELETE][$key]);
		}
		$tables = count($parts[Query::DELETE]) > 0 ? ' '.implode(', ', $parts[Query::DELETE]).' ' : ' ';

		return
			self::SQL_DELETE . $tables .
			self::_renderFrom ($query, false) . ' ' .
			self::_renderWhere ($query);
	}

	public function _renderFrom (Query $query, $use_alias = true)
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
			if ($from [Query::TABLE] instanceof Query)
			{
				$table = '(' . $this->_renderSelect ($from [Query::TABLE]) . ')';
			}
			else
			{
				$table =
					strpos ($from [Query::TABLE], self::SQL_ESCAPE) !== false ?
					$from [Query::TABLE] :
					Model_Scheme::table ($from [Query::TABLE]);

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
		return $sql;
	}

	public function _renderHaving (Query $query)
	{
		$having = $query->part (Query::HAVING);
		
		if (empty ($having))
		{
			return '';
		}
		return  
			self::SQL_HAVING . ' ' . $having;
	}
	
	public function _renderGroup (Query $query)
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
	 * @desc Рендеринг INSERT запроса.
	 * @param Query $query Запрос.
	 * @return string Сформированный SQL запрос.
	 */
	public function _renderInsert (Query $query)
	{
		$table = $query->part (Query::INSERT);
		$sql = 'INSERT ' . strtolower (Model_Scheme::table ($table)) . ' (';

		$fields = array_keys ($query->part (Query::VALUES));
		$values = array_values ($query->part (Query::VALUES));

		for ($i = 0, $icount = count ($fields); $i < $icount; $i++)
		{
			$fields [$i] = self::_escape ($fields [$i]);
			$values [$i] = self::_quote ($values [$i]);
		}

		$fields = implode (', ', $fields);
		$values = implode (', ', $values);

		return $sql . $fields . ') VALUES (' . $values . ')';
	}

	public function _renderLimitoffset (Query $query)
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

	public function _renderOrder (Query $query)
	{
		$orders = $query->part (Query::ORDER);
		if (!$orders)
		{
			return '';
		}

		$columns = array ();
		foreach ($orders as $order)
		{
			$field = explode (self::SQL_DOT, $order [0]);
			$field = array_map (array($this, '_escape'), $field);
			$field = implode (self::SQL_DOT, $field);

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
	 * @desc Рендеринг REPLACE запроса.
	 * @param Query $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function _renderReplace (Query $query)
	{
		$table = $query->part (Query::REPLACE);
		$sql = 'REPLACE ' . strtolower (Model_Scheme::table ($table)) . ' (';

		$fields = array_keys ($query->part (Query::VALUES));
		$values = array_values ($query->part (Query::VALUES));

		for ($i = 0, $icount = count ($fields); $i < $icount; $i++)
		{
			$fields [$i] = self::_escape ($fields [$i]);
			$values [$i] = self::_quote ($values [$i]);
		}

		$fields = implode (', ', $fields);
		$values = implode (', ', $values);

		return $sql . $fields . ') VALUES (' . $values . ')';
	}

	/**
	 * @desc Рендеринг SELECT запроса.
	 * @param Query $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function _renderSelect (Query $query)
	{
		$sql = '';

		$parts = $query->parts ();

		if ($parts [Query::SELECT])
		{
			$sql =
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

		return $sql . implode (self::SQL_COMMA, $columns) . ' ' .
			self::_renderFrom ($query) . ' ' .
			self::_renderUseIndex ($query) . ' ' .
			self::_renderWhere ($query) . ' ' .
			self::_renderGroup ($query) . ' ' .
			self::_renderHaving ($query) . ' ' .
			self::_renderOrder ($query) . ' ' .
			self::_renderLimitoffset ($query);
	}

	/**
	 * @desc Рендер части SHOW.
	 * @param Query $query Запрос.
	 * @return string Сформированный запрос.
	 */
	public function _renderShow (Query $query)
	{
		$sql = 'SHOW ' . $this->_partDistinct ($query) . ' ';

		$sql .= $query->part (Query::SHOW);

		return $sql . ' ' .
			self::_renderFrom ($query, false) . ' ' .
			self::_renderWhere ($query) . ' ' .
			self::_renderOrder ($query) . ' ' .
			self::_renderLimitoffset ($query) . ' ' .
			self::_renderGroup ($query);
	}

	/**
	 * @desc Рендеринг UPDATE запроса.
	 * @param Query $query Запрос.
	 * @return Сформированный SQL запрос.
	 */
	public function _renderUpdate (Query $query)
	{
		$table = $query->part (Query::UPDATE);
		$sql =
			'UPDATE ' .
			strtolower (Model_Scheme::table ($table)) .
			' SET ';

		$values = $query->part (Query::VALUES);
		$sets = array();

		foreach ($values as $field => $value)
		{
			if (
				strpos ($field, '?') !== false ||
				strpos ($field, '=') !== false
			)
			{
				$sets [] = str_replace ('?', $this->_quote ($value), $field);
			}
			else
			{
				$sets [] = self::_escape ($field) . '=' . $this->_quote ($value);
			}
		}

		return $sql . implode (', ', $sets) . ' ' . $this->_renderWhere ($query);
	}

	public function _renderUseIndex (Query $query)
	{
		$indexes = $query->part (Query::INDEX);
		if (!$indexes)
		{
			return '';
		}

		return 'USE INDEX(' . implode (',', $indexes) . ')';
	}

	public function _renderWhere (Query $query)
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
				if ($where [Query::VALUE] instanceof Query)
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
				if ($where [Query::WHERE] instanceof Query)
				{
					$w = $where [Query::WHERE]->getPart (Query::WHERE);
					$w [0]['empty'] = true;
					$where [Query::WHERE]->setPart (Query::WHERE, $w);
					$sql .= '(' . $this->_renderSelect ($where [Query::WHERE]) . ')';
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
	 *
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
//				if (
//					strpos ($value, '(') === false &&
//					strpos ($value, ')') === false
//				)
//				{
//					$value = $this->_quote ($value);
//				}

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

//			return str_replace (
//				self::WHERE_VALUE_CHAR,
//				is_array ($value)
//					? $this->_renderInArray ($value)
//					: $this->_quote ($value),
//					: (
//						strpos ($value, '(') !== false ||
//						strpos ($value, ')') !== false
//							? $value
//							: $this->_quote ($value)
//					),
//				$condition
//			);
		}
	}

}