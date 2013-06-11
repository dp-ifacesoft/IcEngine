<?php

/**
 * Транслятор в SQL запрос
 *
 * @author goorus, morph, neon
 */
class Query_Translator_Mysql_Select extends Query_Translator_Abstract
{
	// Для построения SQL запроса
	const SQL_AND               = 'AND';
	const SQL_ASC               = 'ASC';
	const SQL_COMMA             = ',';
	const SQL_DELETE            = 'DELETE';
	const SQL_DESC              = 'DESC';
	const SQL_DISTINCT          = 'DISTINCT';
	const SQL_EXPLAIN           = 'EXPLAIN';
	const SQL_DOT               = '.';
	const SQL_EQUAL             = '=';
	const SQL_ESCAPE            = '`';
	const SQL_FROM              = 'FROM';
	const SQL_GROUP_BY          = 'GROUP BY';
	const SQL_HAVING            = 'HAVING';
	const SQL_IN                = 'IN';
	const SQL_INSERT            = 'INSERT';
	const SQL_INNER_JOIN        = 'INNER JOIN';
	const SQL_LEFT_JOIN         = 'LEFT JOIN';
	const SQL_RIGHT_JOIN        = 'RIGHT JOIN';
	const SQL_LIMIT             = 'LIMIT';
	const SQL_LIKE              = 'LIKE';
	const SQL_ON                = 'ON';
	const SQL_ORDER_BY          = 'ORDER BY';
	const SQL_QUOTE             = '"';
	const SQL_REPLACE           = 'REPLACE';
	const SQL_SELECT            = 'SELECT';
	const SQL_SET               = 'SET';
	const SQL_SHOW              = 'SHOW';
	const SQL_RLIKE             = 'RLIKE';
	const SQL_UPDATE            = 'UPDATE';
	const SQL_VALUES            = 'VALUES';
	const SQL_WHERE             = 'WHERE';
	const SQL_WILDCARD          = '*';
	const SQL_CALC_FOUND_ROWS   = 'SQL_CALC_FOUND_ROWS';
	const WHERE_VALUE_CHAR      = '?';
    const SQL_NULL              = 'NULL';
    const SQL_AS                = 'AS';

    /**
     * Хелпер транслятора
     *
     * @var Helper_Query_Translator_Mysql
     */
    protected static $helper;

    /**
	 * Рендеринг SELECT запроса.
	 *
     * @param Query_Abstract $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function doRenderSelect(Query_Abstract $query)
	{
		$sql = $this->renderSelect($query);
		return $sql . ' ' .
			$this->renderFrom($query) . ' ' .
			$this->renderWhere($query) . ' ' .
			$this->renderGroup($query) . ' ' .
			$this->renderHaving($query) . ' ' .
			$this->renderOrder($query) . ' ' .
			$this->renderLimitoffset($query);
	}

    /**
     * Получить имя таблицы для части from запроса
     *
     * @param Query_Select $from
     * @return string
     */
    protected function fromTable($from)
    {
        $modelScheme = $this->modelScheme();
        if ($from[Query::TABLE] instanceof Query_Select) {
            $table = '('. $this->renderSelect($from[Query_Select::TABLE]). ')';
        } else {
            $unescapedTable =
                strpos($from[Query::TABLE], self::SQL_ESCAPE) !== false
                    ? $from[Query::TABLE]
                    : $modelScheme->table($from[Query::TABLE]);
            $table = $this->helper()->escape($unescapedTable);
        }
        return $table;
    }

    /**
     * Получить (инициализировать) хелпер траслятора
     *
     * @return Helper_Query_Translator_Mysql
     */
    public function helper()
    {
        if (is_null(self::$helper)) {
            self::$helper = IcEngine::serviceLocator()->getService(
                'helperQueryTranslatorMysql'
            );
        }
        return self::$helper;
    }

    /**
     * Нормализовать часть запроса from
     *
     * @param array $from
     * @return array
     */
    protected function normalizeFrom($from)
    {
        if (count($from) == 1) {
            return $from;
        }
        foreach ($from as $alias => $table) {
            if ($table[Query::JOIN] != Query::FROM) {
                continue;
            }
            unset($from[$alias]);
            $from = array_merge(array($alias => $table), $from);
            break;
        }
        return $from;
    }

    /**
	 * Рендерит часть SQL CALC FOUND ROWS
     *
	 * @param Query_Abstract $query
	 * @return string
	 */
	protected function partCalcFoundRows(Query_Abstract $query)
	{
		return $query->part(Query::CALC_FOUND_ROWS)
            ? self::SQL_CALC_FOUND_ROWS : '';
	}

    /**
	 * Рендерит часть distinct
     *
	 * @param Query_Abstract $query
	 * @return string
	 */
	protected function partDistinct(Query_Abstract $query)
	{
		return $query->part(Query::DISTINCT) ? self::SQL_DISTINCT : '';
	}

    /**
     * Рендеринг части запроса explain
     *
     * @param Query_Abstract $query
     * @return string
     */
	protected function partExplain(Query_Abstract $query)
	{
		return $query->part(Query::EXPLAIN) ? self::SQL_EXPLAIN : '';
	}

    /**
     * Отрендерить условие с "?"
     *
     * @param string $condition
     * @param mixed $value
     * @return string
     */
    protected function quoteConditionMasked($condition, $value)
    {
        $charPos = 0;
        $values = (array) $value;
        $i = 0;
        $helper = $this->helper();
        while ($charPos !== false) {
            $charPos = strpos($condition, self::WHERE_VALUE_CHAR, $charPos);
            if ($charPos === false) {
                break;
            }
            if (!array_key_exists($i, $values)) {
                break;
            }
            $value = is_array($values[$i])
                ? $this->renderInArray($values[$i])
                : $helper->quote($values[$i]);
            $left = substr($condition, 0, $charPos);
            $right = substr($condition, $charPos + 1);
            $condition = $left . $value . $right;
            $charPos += strlen($value);
            $i++;
        }
        return $condition;
    }

    /**
     * Рендеринг отложенных условий
     *
     * @param string $condition
     * @param array $value
     * @return string
     */
    protected function qouteConditionPrepared($condition, $value)
    {
        if (!is_array($value)) {
            return $this->quoteConditionSimple($condition, $value);
        }
        $helper = $this->helper();
        foreach ($value as $key => $keyValue) {
            if (is_numeric($key)) {
                continue;
            }
            $key = ':' . $key;
            $keyValue = is_array($keyValue)
                ? $this->renderInArray($keyValue)
                : $helper->quote($keyValue);
            $condition = str_replace($key, $keyValue, $condition);
        }
        return $condition;
    }

    /**
     * Отрендерить условия без "?"
     *
     * @param string $condition
     * @param mixed $value
     * @return string
     */
    protected function quoteConditionSimple($condition, $value)
    {
        $helper = $this->helper();
        if (is_array($value)) {
            $value = self::SQL_IN . ' (' . $this->renderInArray($value) . ')';
        } else {
            $value = '=' . $helper->quote($value);
        }
        if ($helper->isExpression($condition) ||
            $helper->isEscaped($condition)) {
            return $condition . $value;
        } elseif (strpos($condition, self::SQL_DOT) !== false) {
            return $helper->escapePartial($condition) . $value;
        } else {
            return $helper->escape($condition) . $value;
        }
    }

    /**
	 * Экранирование условий запроса
	 *
     * @param string $condition
	 * @param mixed $value[optional]
	 * @return string
	 */
	protected function quoteCondition($condition)
	{
		if (func_num_args() == 1) {
			return $condition;
		}
		$value = func_get_arg(1);
        if (strpos($condition, ':') !== false) {
            return $this->quoteConditionPrepared($condition, $value);
        } elseif (strpos($condition, self::WHERE_VALUE_CHAR) !== false) {
            return $this->quoteConditionMasked($condition, $value);
        } else {
            return $this->quoteConditionSimple($condition, $value);
        }
	}

    /**
     * Отрендерить часть from с join
     *
     * @param string $alias
     * @param string $table
     * @param array $from
     * @return string
     */
    protected function renderFromJoin($alias, $table, $from)
    {
        if (is_array($from[Query::WHERE])) {
            $helper = $this->helper();
            $where =
                $helper->escape($from[Query::WHERE][0]) .
                self::SQL_DOT .
                $helper->escape($from[Query::WHERE][1]) .
                '=' .
                $helper->escape($from[Query::WHERE][2]) .
                self::SQL_DOT .
                $helper->escape($from[Query::WHERE][3]);
        } else {
            $where = $from[Query::WHERE];
        }
        return ' ' .
            $from[Query::JOIN] . ' ' .
            $table . ' ' . self::SQL_AS . ' ' . $alias . ' ' .
            self::SQL_ON .
            '(' . $from[Query::WHERE] . ')';
    }

    /**
     * Отрендерить обыкновенный from
     *
     * @param string $alias
     * @param string $table
     * @param boolean $useAlias
     * @param integer $i
     * @return string
     */
    protected function renderFromSimple($alias, $table, $useAlias, $i)
    {
        $a = ($table == $alias || !$useAlias);
        return
            ($i ? self::SQL_COMMA : ' ') .
            ($a ? $table : ($table . ' ' . self::SQL_AS . ' ' . $alias));
    }

	/**
	 * Рендерит часть запроса from
     *
	 * @param Query_Abstract $query
	 * @param type $useAlias
	 * @return string
	 */
	protected function renderFrom(Query_Abstract $query, $useAlias = true)
	{
		$sql = self::SQL_FROM;
		$i = 0;
        $from = $query->part(Query::FROM);
		if (!$from) {
			return;
		}
        $helper = $this->helper();
		$froms = $this->normalizeFrom($from);
		foreach ($froms as $alias => $from) {
            $table = $this->fromTable($from);
			$alias = $helper->escape($alias);
			if ($from[Query::JOIN] == Query::FROM) {
                $part = $this->renderFromSimple($alias, $table, $useAlias, $i);
			} else {
                $part = $this->renderFromJoin($alias, $table, $from);
            }
            $sql .= $part;
			$i++;
		}
		return $sql .
			$this->renderUseIndex($query) .
			$this->renderForceIndex($query);
	}

	/**
	 * Рендерит часть запроса FORCE INDEX
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderForceIndex(Query_Abstract $query)
	{
		return $this->renderUseIndex($query);
	}

	/**
	 * Рендерит часть запроса group
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderGroup(Query_Abstract $query)
	{
		$groups = $query->part(Query::GROUP);
        if (!$groups) {
            return;
        }
		$columns = array();
        $helper = $this->helper();
		foreach ($groups as $column) {
            $columns[] = $helper->escapePartial(reset($column));
		}
		return self::SQL_GROUP_BY . ' ' .
			implode(self::SQL_COMMA, $columns);
	}

    /**
     * Рендеринг части запроса "having"
     *
     * @param Query_Abstract $query
     * @return string
     */
	protected function renderHaving(Query_Abstract $query)
	{
		$having = $query->part(Query::HAVING);
        if (!$having) {
            return;
        }
		return self::SQL_HAVING . ' ' . 
            (is_array($having) ? reset($having) : $having);
	}

	/**
	 * Рендерит mysql терм если он массив
	 *
     * @param array $value
	 * @return string
	 */
	protected function renderInArray(array $value)
	{
		if (!$value) {
			return self::SQL_NULL;
		}
        $callable = array($this->helper(), 'quote');
        $valueMapped = array_map($callable, (array) $value);
		return implode(self::SQL_COMMA, $valueMapped);
	}

	/**
	 * Рендер части запроса limit
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderLimitoffset (Query_Abstract $query)
	{
        $limit = $query->part(Query::LIMIT);
        if (!$limit) {
            return;
        }
        if (empty($limit[Query::LIMIT_COUNT])) {
            return;
        }
        $count = $limit[Query::LIMIT_COUNT];
        $offset = !empty($limit[Query::LIMIT_OFFSET])
            ? $limit[Query::LIMIT_OFFSET] : 0;
        return self::SQL_LIMIT . ' ' . $offset . self::SQL_COMMA . $count;
	}

	/**
	 * Рендер части запроса order
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderOrder(Query_Abstract $query)
	{
		$orders = $query->part(Query::ORDER);
        if (!$orders) {
            return;
        }
		$columns = array();
        $helper = $this->helper();
		foreach ($orders as $order) {
			$column = $helper->escapePartial($order[0]);
			if ($order[1] == self::SQL_DESC) {
				$column = $column . ' ' . self::SQL_DESC;
			}
            $columns[] = $column;
		}
		return self::SQL_ORDER_BY . ' ' . implode(self::SQL_COMMA, $columns);
	}

    /**
     * Отрендерить часть выборки в массиве
     *
     * @param array $parts
     * @return string
     */
    protected function renderSelectArray($parts)
    {
        $source = null;
        $helper = $this->helper();
        if (count($parts) > 1) {
            if (!empty($parts[0])) {
                $source = $helper->escape($parts[0]) . self::SQL_DOT;
            }
            if (!$helper->isEscaped($parts[1]) &&
                !$helper->isExpression($parts[1])) {
                $source .= $helper->escapePartial($parts[1]);
            } else {
                $source .= $parts[1];
            }
        } elseif (strpos($parts[0], self::SQL_WILDCARD) !== false) {
            $source = $parts[0];
        } else {
            $source = $helper->escapePartial($parts[0]);
        }
        return $source;
    }

    /**
     * Рендерить часть запроса select
     *
     * @param Query_Abstract $query
     * @return string
     */
    protected function renderSelect(Query_Abstract $query)
    {
		$select = $query->part(Query::SELECT);
        if (!$select) {
            return;
        }
        $sql = $this->partExplain($query) . ' ' . self::SQL_SELECT . ' ' .
            $this->partCalcFoundRows($query) . ' ' .
            $this->partDistinct($query);
        $columns = array();
        $helper = $this->helper();
        foreach ($select as $alias => $parts) {
            if (is_array($parts)) {
                $source = $this->renderSelectArray($parts);
            } elseif (strpos($parts, self::SQL_COMMA) !== false) {
                $subParts = explode(self::SQL_COMMA, $parts);
                $subPartsTrimed = array_map('trim', $subParts);
                $callable = array($helper, 'escapePartial');
                $sourceUnjoined = array_map($callable, $subPartsTrimed);
                $source = implode(self::SQL_COMMA, $sourceUnjoined);
                $alias = $source;
            } elseif (strpos($parts, self::SQL_WILDCARD) === false &&
                !$helper->isExpression($parts))  {
                $source = $helper->escapePartial($parts);
            } elseif (strpos($parts, self::SQL_WILDCARD) !== false) {
                $subParts = explode(self::SQL_DOT, $parts);
                $partsMapped = array_map(array($helper, 'escape'), $subParts);
                $source = implode(self::SQL_DOT, $partsMapped);
            } else {
                $source = $parts;
            }
            if (is_numeric($alias) || $helper->isExpression($alias) ||
                strpos($alias, self::SQL_WILDCARD) !== false) {
                $columns[] = $source;
            } else {
                if (strpos($alias, self::SQL_DOT) !== false) {
                    list(,$alias) = explode('.', $alias);
                }
                $alias = $helper->escape($alias);
                if ($alias == $source) {
                    $columns[] = $source;
                } else {
                    $columns[] = $source . ' ' . self::SQL_AS . ' ' . $alias;
                }

            }
        }
        return $columns ? $sql . implode(self::SQL_COMMA, $columns) : '';
    }

	/**
	 * Рендерит часть запроса USE INDEX
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderUseIndex(Query_Abstract $query)
	{
		$indexes = $query->part(Query::INDEX);
		if (!$indexes) {
			return;
		}
		return $indexes[1] . '(' . implode(',', (array) $indexes[0]) . ')';
	}

    /**
     * Отрендерить условие без значения
     *
     * @param array $where
     * @return string
     */
    protected function renderWhereWithoutValue($where)
    {
        if ($where[Query::WHERE] instanceof Query_Select) {
            $subWhere = $where[Query::WHERE]->getPart(Query::WHERE);
            $subWhere[0]['empty'] = true;
            $where[Query::WHERE]->setPart(Query::WHERE, $subWhere);
            return '(' . $this->doRenderSelect($where[Query::WHERE]) . ')';
        } else {
            return $this->quoteCondition($where[Query::WHERE]);
        }
    }

    /**
     * Отрендерить часть условия со значением
     *
     * @param array $where
     * @return string
     */
    protected function renderWhereWithValue($where)
    {
        if ($where[Query::VALUE] instanceof Query_Select) {
            $where[Query::VALUE] = '(' .
                $this->doRenderSelect($where[Query::VALUE]) . ')';
        }
        $value = $where[Query::WHERE];
        return $this->quoteCondition($value, $where[Query::VALUE]);
    }

	/**
	 * Рендер части запроса Where
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderWhere(Query_Abstract $query)
	{
		$wheres = $query->part(Query::WHERE);
		if (!$wheres) {
			return;
		}
		$sql = self::SQL_WHERE . ' ';
		foreach ($wheres as $i => $where) {
			if (!empty($where['empty'])) {
				$sql = '';
                break;
			}
		}
		foreach ($wheres as $i => $where) {
			if ($i > 0) {
				$sql .= ' ' . $where[0] . ' ';
			}
			if (array_key_exists(Query::VALUE, $where)) {
				$sql .= $this->renderWhereWithValue($where);
			} else {
				$sql .= $this->renderWhereWithoutValue($where);
			}
		}
		return $sql;
	}
}