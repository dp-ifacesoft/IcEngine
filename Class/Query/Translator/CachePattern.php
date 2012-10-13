<?php

class Query_Translator_CachePattern extends Query_Translator
{
	
	/**
	 * Разделитель 
	 * @var string
	 */
	public $cache_base_delim = '\\';
	
	/**
	 * 
	 * @param string $table
	 * @param integer $index_num
	 * @param string $index_use
	 * @param array $values
	 * @return string
	 */
	protected function _pattern ($table, $index_num, $index_use, array $values)
	{
		$pattern = $table . 'I' . $index_num . self::$cache_base_delim;
		for ($i = 0; $i < strlen($index_use); $i++)
		{
			if (array_key_exists($i, $values))
			{
				$pattern .= ':' . $values[$i];
			}
			else
			{
				$pattern .= ':*';
			}
		}
		return $pattern . ':*';
	}
	
	/**
	 * 
	 * @param Query $query
	 * @return string
	 */
	public function translate (Query $query)
	{
	    $parts = $query->parts ();
	    $table = '';
		$joins = array ();
		$jalias = array ();
		
		foreach ($parts [Query::FROM] as $alias => $from)
		{
			if ($from [Query::JOIN] == Query::FROM)
			{
				if (!empty ($table))
				{
					return false;
				}
				$table = $from [Query::FROM];
			}
			elseif ($from [Query::JOIN] == Query::INNER_JOIN)
			{
				if (
					!is_array ($from [Query::WHERE]) || 
					count ($from [Query::WHERE]) != 4
				)
				{
					return false;
				}
				$joins []= 
					$from [Query::FROM] . ':' .
					$from [Query::WHERE] [1] . '=' .
					$from [Query::WHERE] [3] . ';';
			}
			else
			{
				return false;
			}
			$jalias [$alias] = $from [Query::FROM];
		}
		
		$indexes = Model_Scheme::indexes ($table, $joins);
		
		if ($indexes === false)
		{
			return false;
		}

		if (empty ($parts [Query::WHERE]))
		{
			return $table . 'II' . self::$cache_base_delim . '*';
		}
		
		if (empty ($indexes))
		{
			return false;
		}
		
		// Покрытие индексом запроса
		// Изначально строка "11111", по мере использования,
		// 1 заменяются на 0.
		$index_use = array();
		// Значения для полей индекса
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
		
//		$matches = array ();
//		preg_match_all ('/([^\.]+)?\.?(.*?)(\!\=|\>\=|\<=|\<|\>|\=)\"(.*?)\"$/', $args, $matches);
//		if (!empty ($matches [2][0]) && !empty ($matches [3][0]) && isset ($matches [4][0]))
//		{
//			if (!empty ($matches [1][0]))
//			{
//				$args = join ('', array ($this->escape ($matches [1][0]), self::SQL_DOT, $this->escape ($matches [2][0]), $matches [3][0], $this->escape ($matches [4][0], self::SQL_QUOTE)));
//			}
//			else
//			{
//				$args = join ('', array ($this->escape ($matches [2][0]), $matches [3][0], $this->escape ($matches [4][0], self::SQL_QUOTE)));
//			}
//		}
//		$parts = (array) $this->escape ($args, '');
		
		// Запоминаем значения для полей индекса
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
				// Получаем таблицу и колонку
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
		
		// выбираем индекс
		$best_v = 0;
		$best_i = 0;
		foreach ($index_use as $ii => $use)
		{
			$v = strpos($use, '1');
			
			if ($v === false)
			{
				// Индекс полностью покрывает запрос
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
	
}