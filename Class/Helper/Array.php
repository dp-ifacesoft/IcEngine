<?php

/**
 * Помощник работы с массивами
 *
 * @author goorus, morph, neon
 * @Service("helperArray")
 */
class Helper_Array
{
	/**
	 * Возвращает массив
     *
	 * @param array $input Двумерный массив.
	 * @param string $columnNames Название колонки.
	 * @param string $indexName Имя индекса
	 * @return array Колонка $column исходного массива
	 */
	public function column($input, $columnNames, $indexName = null)
	{
        if (!$columnNames) {
            return $input;
        }
        if (!is_array($input) || empty($input)) {
            return array();
        }
		$result = array();
        $count = count($columnNames);
		foreach ($input as $row) {
            $current = array();
            foreach ((array) $columnNames as $column) {
                $value = isset($row[$column]) ? $row[$column] : null;
                if ($count > 1) {
                    $current[$column] = $value;
                } else {
                    $current = $value;
                }
            }
			if ($indexName && isset($row[$indexName])) {
				$result[$row[$indexName]] = $current;
			} else {
				$result[] = $current;
			}
		}
		return $result;
	}

    /**
     * Фильтрация массива
     *
     * @param array $rows
     * @param array $filter
     * @return array
     */
    public function filter($rows, $filter)
    {
		$firstFields = array();
		foreach ($filter as $field => $value) {
			$s = substr($field, -2, 2);
			if ($s[0] == '=' || ctype_alnum($s)) {
				unset($filter[$field]);
				$field = str_replace(' ', '', rtrim($field, '='));
				$firstFields[$field] = $value;
			}
		}
        $result = array();
		foreach ($rows as $row) {
			$valid = true;
			if ($firstFields && !self::validateRow($row, $firstFields)) {
                continue;
            }
			foreach ($filter as $field => $value) {
                $fieldModificator = false;
                if (strpos($field, '<') || strpos($field, '>') || strpos($field, '!')) {
                    $fieldModificator = true;
                }
                if (!isset($row[$field]) && !$fieldModificator) {
                    $valid = false;
                    break;
                }
                $field = str_replace(' ', '', $field);
                $s = substr($field, -2, 2);
                $offset = 2;
                if (ctype_alnum($s)) {
                    $s = '=';
                    $offset = 0;
                } elseif(ctype_alnum($s [0])) {
                    $s = $s[1];
                    $offset = 1;
                }
                if ($offset) {
                    $field = substr($field, 0, -1 * $offset);
                }
                $currentValid = 0;
                switch ($s) {
                    case '>': $currentValid = ($row[$field] > $value); break;
                    case '>=': $currentValid = ($row[$field] >= $value); break;
                    case '<': $currentValid = ($row[$field] < $value); break;
                    case '<=': $currentValid = ($row[$field] <= $value); break;
                    case '!=': $currentValid = ($row[$field] != $value); break;
                }
                $valid &= $currentValid;
                if (!$valid) {
                    break;
                }
			}
            if ($valid) {
                $result[] = $row;
            }
		}
		return $result;
    }

    /**
     * @desc Помечает массив для разбиения по коллонкам.
     * @param array $content Данные
     * @param $colsCount
     * @param $startMark
     * @param $finishMark
     * @param null $blockMark
     * @internal param int $cols_count На сколько колонок разбить*        На сколько колонок разбить
     * @internal param string $start_mark Как отмечать начала колонки.*        Как отмечать начала колонки.
     *        Это поле поле будет установлено в true, у записей из $content,
     *        которые являются началом колонки.
     * @internal param string $finish_mark Как отмечать завершение колонки.*        Как отмечать завершение колонки.
     *        Это поле будет установлено в true, у записей из $content,
     *        которые являются концом колонки
     * @internal param string $block_mark Признак начала неделимого блока.*        Признак начала неделимого блока.
     *        Если записи из $content, идущие подряд, имеют одинаковое поле $bock_mark,
     *        разбиение между ними не будет.
     */
	public function markForColumns ( &$content, $colsCount,
		$startMark, $finishMark, $blockMark = null
	)
	{
		$rowsCount = count ($content);
		if ($rowsCount < 1)
		{
			return;
		}
		if ($rowsCount == 1)
		{
			$content[$startMark] = true;
			$content[$finishMark] = true;
		}

		$inColumn = ceil($rowsCount / $colsCount);

		if (empty($blockMark))
		{
			// без блоков
			$index = $inColumn;
			$content[0][$startMark] = true;
			while ($index < $rowsCount)
			{
				$content[$index][$startMark] = true;
				$content[$index - 1][$finishMark] = true;
				$index += $inColumn;
			}
			$content [$rowsCount - 1][$finishMark] = true;
			return;
		}

		// по блокам
		$nextColumnFinish = $inColumn;
		$index = 1;
		$content[0][$startMark] = true;
		$index++;
		while ($index < $rowsCount) {
			if (
				$index >= ($nextColumnFinish) &&
				isset($content[$index][$blockMark])
			)
			{
				$content[$index - 1][$finishMark] = true;
				$content[$index][$startMark] = true;
				//fb($index);
				$nextColumnFinish += $inColumn;
			}
			$index++;
		}
		$content[$rowsCount - 1][$finishMark] = true;
	}

	/**
	 * @desc Сортирует многомерный массив по заданным полям
	 * @param array $data Массив
	 * @param string $sortby Поля сортировки через запятую
	 * @return boolean true если успешно, иначе false.
	 */
	public function masort($data, $sortby)
	{
		static $funcs = array();

		if (empty($funcs[$sortby]))
		{
			//Не существует функции сравнения, создаем
			$code = "\$c=0;";
			foreach (explode(',', $sortby) as $key)
			{
				$key = trim($key);
				if (strlen($key) > 5 && substr($key, -5) == ' DESC') {
					$asc = false;
					$key = substr($key, 0, strlen ($key) - 5);
				}
				else {
					$asc = true;
				}
				reset($data);
				$array = current($data);
				if (is_numeric($array[$key])) {
					$code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0 : ((\$a['$key'] " . (($asc) ? '<' : '>') . " \$b['$key']) ? -1 : 1 )) ) return \$c;";
				}
				else {
					$code .= "if ( (\$c = strcasecmp(\$a['$key'], \$b['$key'])) != 0 ) return " . (($asc) ? '' : '-') . "\$c;\n";
				}

			}
			$code .= 'return $c;';
	//		predump($code);
			// $c=0;if ( $c = (($a['rank'] == $b['rank']) ? 0 : (($a['rank'] < $b['rank']) ? -1 : 1 )) ) return $c;return $c;
			$funcs[$sortby] = create_function('$a, $b', $code);
		}

		uasort($data, $funcs[$sortby]);
		return $data;
	}

	/**
	 * Merges any number of arrays of any dimensions, the later overwriting
	 * previous keys, unless the key is numeric, in whitch case, duplicated
	 * values will not be added.
	 *
	 * The arrays to be merged are passed as arguments to the function.
	 *
	 * @access public
	 * @return array Resulting array, once all have been merged
	 */
	public function mergeReplaceRecursive()
	{
		// Holds all the arrays passed
		$params = func_get_args();
		// First array is used as the base, everything else overwrites on it
		$return = array_shift($params);
		// Merge all arrays on the first array
		foreach ($params as $array)
		{
			foreach ($array as $key => $value)
			{
				// Numeric keyed values are added (unless already there)
				if (is_numeric ($key) && !in_array ($value, $return)) {
					if (is_array ($value)) {
						$return[] = $this->mergeReplaceRecursive($return[$key], $value);
					}
					else {
						$return[] = $value;
					}
				}
				else {
					// String keyed values are replaced
					if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
						$return[$key] = $this->mergeReplaceRecursive($return[$key], $value);
					}
					else {
						$return[$key] = $value;
					}
				}
			}
		}
		return $return;
	}

	/**
	 * Сортирует массив объектов по заданным полям
     *
	 * @param array $data Массив объектов
	 * @param string $sortby Поля для сортировки
     * @return bool
     */
	public function mosort(&$data, $sortby)
	{
		if (count($data) <= 1) {
			return true;
		}
		static $funcs = array();
		if (empty ($funcs[$sortby])) {
			//Не существует функции сравнения, создаем
			$code = "\$c=0;";
			foreach (explode(',', $sortby) as $key) {
				$key = trim($key);
				if (strlen($key) > 5 && substr($key, -5) == ' DESC') {
					$asc = false;
					$key = substr ($key, 0, strlen($key) - 5);
				}
				else {
					$asc = true;
				}
				reset($data);
				$object = current($data);
				if (is_numeric ($object->{$key})) {
					$code .= "if ( \$c = ((\$a->$key == \$b->$key) ? 0 : ((\$a->$key " . (($asc) ? '<' : '>') . " \$b->$key) ? -1 : 1 )) ) return \$c;";
				}
				else {
					$code .= "if ( (\$c = strcasecmp(\$a->$key, \$b->$key)) != 0 ) return " . (($asc) ? '' : '-') . "\$c;\n";
				}

			}
			$code .= 'return $c;';
	//		fb($code);
	//		$c=0;if ( $c = (($a->rank == $b->rank) ? 0 : (($a->rank < $b->rank) ? -1 : 1 )) ) return $c;return $c;
			$funcs[$sortby] = create_function('$a, $b', $code);
		}

		return uasort($data, $funcs [$sortby]);
	}

	/**
	 * @desc Выбор только значений, начинающихся с префикса
	 * @param array $array
	 * @param string $prefix
	 * @return array
	 */
	public static function prefixed (array $array, $prefix)
	{
		$len = strlen($prefix);
		$result = array();
		foreach ($array as $k => $v) {
			if (strncmp ($k, $prefix, $len) == 0) {
				$k = substr($k, $len);
				$result[$k] = $v;
			}
		}
		return $result;
	}

    /**
     * Переиндексировать массив по полю
     *
     * @param array $array
     * @param string $field
     * @return array
     */
    public function reindex($array, $field = 'id')
    {
        if (!is_array($array) || empty($array)) {
            return $array;
        }
        $arrayElementFields = array_keys(reset($array));
        $arrayElementFieldsFlipped = array_flip($arrayElementFields);
        if (!isset($arrayElementFieldsFlipped[$field])) {
            return $array;
        }
        return $this->column($array, $arrayElementFields, $field);
    }

	/**
	 * @desc Установить в качестве ключей массива значения из колонки $column
	 * @param array $input
	 * 		Входной массив.
	 * @param string $column
	 * 		Колонка, значения которой будут использованы в качестве ключей.
	 * @return array
	 */
	public function setKeyColumn (array $input, $column)
	{
		if (!$input) {
			return array();
		}
		return array_combine(
			$this->column($input, $column),
			$input
		);
	}

    /**
     * Проверить ячейку на соответствие фильтру
     *
     * @param array $row
     * @param array $filter
     * @return boolean
     */
    public function validateRow($row, $filter)
    {
		$valid = true;
		foreach ($filter as $field => $value) {
			$value = (array) $value;
            $trimedValue = $value;
            if (is_string(reset($value))) {
                $trimedValue = array_map('trim', $value);
            }
			if (!isset($row[$field]) || !in_array($row[$field], $trimedValue)) {
				$valid = false;
				break;
			}
		}
		return $valid;
    }
}