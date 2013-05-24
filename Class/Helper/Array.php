<?php

/**
 * Помощник работы с массивами
 *
 * @author goorus, morph, neon
 * @Service("helperArray")
 */
class Helper_Array extends Helper_Abstract
{
	/**
	 * Возвращает массив
     *
	 * @param array $input Двумерный массив.
	 * @param string $columns Название колонки.
	 * @param string $index Имя индекса
	 * @return array Колонка $column исходного массива
	 */
	public function column($input, $columns, $index = null)
	{
        if (!$columns) {
            return $input;
        }
        if (!is_array($input) || empty($input)) {
            return array();
        }
		$result = array();
        $count = count($columns);
		foreach ($input as $row) {
            $current = array();
            foreach ((array) $columns as $column) {
                $value = isset($row[$column]) ? $row[$column] : null;
                if ($count > 1) {
                    $current[$column] = $value;
                } else {
                    $current = $value;
                }
            }
			if ($index && isset($current[$index])) {
				$result[$current[$index]] = $current;
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
			if ($firstFields && !$this->validateRow($row, $firstFields)) {
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
	 * Сортирует многомерный массив по заданным полям
	 *
     * @param array $data Массив
	 * @param string $sortby Поля сортировки через запятую
	 * @return boolean true если успешно, иначе false.
	 */
	public function masort($data, $sortby)
	{
        if (!$data) {
            return array();
        }
		static $funcs = array();
		if (empty($funcs[$sortby])) {
			//Не существует функции сравнения, создаем
			$code = "\$c=0;";
			foreach (explode(',', $sortby) as $key) {
				$key = trim($key);
				if (strlen($key) > 5 && substr($key, -5) == ' DESC') {
					$asc = false;
					$key = substr($key, 0, strlen($key) - 5);
				} else {
					$asc = true;
				}
				reset($data);
				$array = current($data);
                $first = reset($array);
                if (!isset($first[$key])) {
                    return $data;
                }
				if (is_numeric($array[$key])) {
					$code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0 : ((\$a['$key'] " . (($asc) ? '<' : '>') . " \$b['$key']) ? -1 : 1 )) ) return \$c;";
				} else {
					$code .= "if ( (\$c = strcasecmp(\$a['$key'], \$b['$key'])) != 0 ) return " . (($asc) ? '' : '-') . "\$c;\n";
				}

			}
			$code .= 'return $c;';
			$funcs[$sortby] = create_function('$a, $b', $code);
		}
		uasort($data, $funcs[$sortby]);
		return $data;
	}

	/**
	 * Сортирует массив объектов по заданным полям
     *
	 * @param array $data Массив объектов
	 * @param string $sortby Поля для сортировки
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
     * Заменить вхождения в строке
     *
     * @param array $data
     * @param array $fields
     */
    public function normalizeFields($data, $fields, $params)
    {
        $helperString = $this->getService('helperString');
        foreach ($data as $i => $item) {
            $data[$i] = $helperString->normalizeFields($item, $fields, $params);
        }
        return $data;
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

    public function unsetColumn($array, $columns = array())
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        foreach ($array as $i => $items) {
            foreach ($items as $j => $value) {
                if (in_array($j, $columns)) {
                    unset($array[$i][$j]);
                }
            }
        }
        return $array;
    }
}