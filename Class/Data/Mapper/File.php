<?php

class Data_Mapper_File extends Data_Mapper_Abstract
{

	protected $_provider;

	/**
	 *
	 * @param Query_Abstract $query
	 * @return array
	 */
	protected function _cachePattern (Query_Abstract $query)
	{
		$table = '';
		$joins = array ();
		$jalias = array ();

		$parts = $query->parts();

		foreach ($parts[Query::FROM] as $alias => $from)
		{
			if ($from[Query::JOIN] == Query::FROM)
			{
				if (!empty($table))
				{
					return false;
				}
				$table = $from[Query::FROM];
			}
			elseif ($from[Query::JOIN] == Query::INNER_JOIN)
			{
				if (
					!is_array($from[Query::WHERE]) ||
					count($from[Query::WHERE]) != 4
				)
				{
					return false;
				}
				$joins[]=
					$from[Query::FROM] . ':' .
					$from[Query::WHERE][1] . '=' .
					$from[Query::WHERE][3] . ';';
			}
			else
			{
				return false;
			}
			$jalias[$alias] = $from[Query::FROM];
		}

		$indexes = $this->_getIndexes ($table);

		if ($indexes === false)
		{
			return false;
		}

		if (empty($parts[Query::WHERE]))
		{
			return $table . 'II' . self::$cache_base_delim . '*';
		}

		if (empty($indexes))
		{
			return false;
		}

		$index_use = array();
		$index_values = array();

		$keys = array_keys($indexes);

		foreach ($keys as $i)
		{
			if (count($indexes[$i]) < count($parts[Query::WHERE]))
			{
				unset($indexes[$i]);
			}
			else
			{
				$index_use[$i] = str_repeat('1', count($indexes[$i]));
				$index_values[$i] = array();
			}
		}

		foreach ($parts[Query::WHERE] as $wi => &$where)
		{
			$cond = $where[Query::WHERE];

			if (!is_scalar($where[Query::VALUE]))
			{
				//predump($where, 'where');
				//trigger_error('Not a scalar type in condition', E_USER_NOTICE);
				return false;
			}

			if (!is_array($cond))
			{
				$cond = explode('.', $cond, 2);
			}

			if (count($cond) == 1)
			{
				$cond[1] = $cond[0];
				$cond[0] = $table;
			}
			elseif (count($cond) != 2)
			{
				return false;
			}

			$cond[0] = trim($cond[0], '`?= ');
			$cond[1] = trim($cond[1], '`?= ');

			if (isset($jalias[$cond[0]]))
			{
				$cond[0] = $jalias[$cond[0]];
			}

			foreach ($indexes as $ii => &$columns)
			{
				foreach ($columns as $ci => &$column)
				{
					if ($cond[0] == $column[0] && $cond[1] == $column[1])
					{
						if (
							!isset($where[Query::TYPE]) ||
							empty($where[Query::TYPE]) ||
							$where[Query::TYPE] == '='
						)
						{
							$index_use[$ii][$ci] = 0;
							$index_values[$ii][$ci] = urlencode($where[Query::VALUE]);
						}
						elseif ($where[Query::TYPE] == 'LIKE')
						{
							$index_use[$ii][$ci] = 0;
							$index_values[$ii][$ci] = str_replace('%25', '*', urlencode($where[Query::VALUE]));
						}

					}
				}
				unset($column);
			}
			unset($columns);
		}

		$best_v = 0;
		$best_i = 0;
		foreach ($index_use as $ii => $use)
		{
			$v = strpos($use, '1');

			if ($v === false)
			{
				return self::_pattern($table, $ii, $use, $index_values[$ii]);
			}

			if ($v > $best_v)
			{
				$best_v = $v;
				$best_i = $ii;
			}
		}

		if ($best_v >= 0)
		{
			$pattern = self::_pattern(
				$table, $best_i,
				$index_use[$best_i], $index_values[$best_i]
			);

			return self::_pattern(
				$table, $best_i,
				$index_use[$best_i], $index_values[$best_i]
			);
		}

		return false;
	}

	/**
	 *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return mixed
	 */
	public function _execute (Query_Abstract $query, $options = null)
	{

	}

	public function __construct ($provider = null)
	{
		if (!$provider)
		{
			$this->_provider = new Data_Provider_FileCache ();
		}
		else
		{
		    $this->_provider = $provider;
		}
	}

	/**
	 *
	 * @param mixed $result
	 * @param mixed $options
	 * @return boolean
	 */
	protected function _isCurrency ($result, $options)
	{
		if (!$options)
		{
			return true;
		}
		return $options->getNotEmpty () && empty ($result) ? false : true;
	}

}