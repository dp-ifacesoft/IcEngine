<?php
/**
 *
 * @desc Транслятор в Mongo запрос
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Query_Translator_Mongo_Select extends Query_Translator_Abstract
{

	const SQL_WILDCARD		= '*';
	const WHERE_VALUE_CHAR	= '?';

	/**
	 * @desc Формирует условие выбора.
	 * OR не поддерживается.
	 * @param Query $query
	 * @return array
	 */
	public function _getCriteria (Query $query)
	{
		$wheres = $query->part (Query::WHERE);

		if (!$wheres)
		{
			return array ();
		}

		$criteria = array ();

		foreach ($wheres as $where)
		{
			$this->_getCriteriaPart ($criteria, $where);
		}

		return $criteria;
	}

	public function _getCriteriaPart (&$criteria, $where)
	{
		static $operations = array (
			'!='		=> '$ne',
			'>='		=> '$gte',
			'<='		=> '$lte',
			'='			=> null,
			'>'			=> '$gt',
			'<'			=> '$lt',
			' NOT IN '	=> '$nin',
			' IN '		=> '$in'
		);

		$w = $where [Query::WHERE];
		$v = true;

		foreach ($operations as $op => $solve)
		{
			$p = strpos ($w, $op);
			if ($p)
			{
				$ok = true;
				$value = trim (substr ($w, $p + strlen ($op)));
				$w = trim (substr ($w, 0, $p));

				// В случае условия вида '? <= age'
				if ($w == '?')
				{
					$temp = $value;
					$value = $w;
					$w = $temp;
				}

				if ($op == '=')
				{
					$criteria [$w] = $value;
					return ;
				}
				elseif (is_string ($solve))
				{
					if (array_key_exists (Query::VALUE, $where))
					{
						$criteria [$w][$solve] = $where [Query::VALUE];
						return ;
					}
					$criteria [$w][$solve] = $value;
					return ;
				}

				throw new Zend_Exception ('Unknown');
				return ;
			}
		}

		if (array_key_exists (Query::VALUE, $where))
		{
			$v = $where [Query::VALUE];
			if (is_array ($v))
			{
				$criteria [$w]['$in'] = $v;
				return ;
			}
		}

		$criteria [$w] = $v;
	}

	/**
	 * @desc Возвращает название коллекции.
	 * @return string
	 */
	public function _getFromCollection (Query $query, $use_alias = true)
	{
		$from = $query->part (Query::FROM);

		if (!$from)
		{
			return;
		}

		if (count ($from) > 1)
		{
			throw new Zend_Exception ('Multi from not supported.');
		}

		//foreach ($from as $alias => $from)

		reset ($from);
		return strtolower (Model_Scheme::table (key ($from)));
	}

	/**
	 *
	 * @param type $query
	 * @return array|null
	 */
	public function _getGroup ($query)
	{
		$group = $query->part (Query::GROUP);

		if (!$group)
		{
			return null;
		}

		throw new Zend_Exception ('Group not supported yet.');
	}

	/**
	 * @desc Сортировка
	 * @param Query $query
	 * @return string
	 */
	public function _getSort (Query $query)
	{
		$orders = $query->part (Query::ORDER);
		if (!$orders)
		{
			return array ();
		}

		$sort = array ();

		foreach ($orders as $order)
		{
			if ($order [1] == Query::DESC)
			{
				$sort [$order [0]] = -1;
			}
			else
			{
				$sort [$order [0]] = 1;
			}
		}

		return $sort;
	}

	/**
	 *
	 * @param Query $query
	 * @return array
	 */
	public function _partCalcFoundRows (Query $query)
	{
		return array (
			'count'	=> (bool) $query->part (Query::CALC_FOUND_ROWS)
		);
	}

	/**
	 * @desc отступ и лимит.
	 * @param Query $query
	 * @return array
	 */
	public function _renderLimitoffset (Query $query)
	{
		$sql = '';
		$limit_count = $query->part (Query::LIMIT_COUNT);

		if ($limit_count)
		{
			return array (
				'skip'	=> (int) $query->part (Query::LIMIT_OFFSET),
				'limit'	=> (int) $query->part (Query::LIMIT_COUNT)
			);
		}
		elseif ($query->part (Query::LIMIT_OFFSET))
		{
			return array (
				'skip'	=> (int) $query->part (Query::LIMIT_OFFSET)
			);
		}

		return array ();
	}

	/**
	 * @desc Рендеринг SELECT (find) запроса.
	 * @param Query $query Запрос
	 * @return string Сформированный Mongo запрос
	 */
	public function _renderSelect (Query $query)
	{
		$fields = array ();

		if (false)
		{
			$select = $query->part (Query::SELECT);

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
					strpos ($sparts, '.') === false &&
					strpos ($sparts, '`') === false
				)
				{
					$source = $this->_escape ($sparts);
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

		return array (
			'collection'	=> self::_getFromCollection ($query),
			'query'			=> self::_getCriteria ($query),
			'fields'		=> $fields,
			'sort'			=> self::_getSort ($query),
			'skip'			=> (int) $query->part (Query::LIMIT_OFFSET),
			'limit'			=> (int) $query->part (Query::LIMIT_COUNT),
			'find_one'		=>
				$query->part (Query::LIMIT_COUNT) == 1 &&
				$query->part (Query::LIMIT_OFFSET) == 0 &&
				!$query->part (Query::ORDER) &&
				!$query->part (Query::CALC_FOUND_ROWS),
			Query::CALC_FOUND_ROWS => $query->part (Query::CALC_FOUND_ROWS)
		);
	}
}