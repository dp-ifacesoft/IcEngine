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
	 * @param string $columnNames Название колонки.
	 * @param string $indexName Имя индекса
	 * @return array Колонка $column исходного массива
	 */
	public function column($input, $columnNames, $indexName = null)
	{
        if (!$columnNames) {
            return $input;
        }
        if (empty($input)) {
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
			$s = substr(trim($field), -2, 2);
            $s = trim($s);
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
                $s = substr($field, -2);
                $offset = 0;
                if(ctype_alnum($s[0])) {
                    $offset = 1;
                }
                $field = substr($field, 0, $offset - 2);
                if($offset) {
                    $s = substr($s, $offset);
                }
                $currentValid = 0;
                switch ($s) {
                    case '>': $currentValid = ($row[$field] > $value); break;
                    case '>=': $currentValid = ($row[$field] >= $value); break;
                    case '<': $currentValid = ($row[$field] < $value); break;
                    case '<=': $currentValid = ($row[$field] <= $value); break;
                    case '!=':
                        if(!is_array($value)) {
                            $currentValid = ($row[$field] != $value);
                        } else {
                            $currentValid = !in_array($row[$field], $value);
                        }
                        break;
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
     * Группировка по полю
     * 
     * @param array $array Массив
     * @param string $fieldName название поля
     * @return array
     */
    public function group($array, $fieldName)
    {
        $groups = array();
        foreach ($array as $item) {
            $groups[$item[$fieldName]][] = $item;
        }
        return $groups;
    }

    /**
     * Вставить в массив значение $what без изменения ключей
     *
     * @param array $array  Массив, в который требуется вставить элемент
     * @param mixed $what   Вставляемый элемент
     * @param int   $offset Смещение, после которого требуется вставить элемент
     * @param string $index Индекс, который должен быть присвоен вставляемому элементу
     *
     * @return array
     */
    public function insertAfter(array $array, $what, $offset, $index = null)
    {
        $insert = $index ? [$index => $what] : $what;
        $where  = $offset + 1;
        $before = array_slice($array,   0,      $where,         TRUE);
        $after  = array_slice($array,   $where, count($array),  TRUE);
        $array  = $before + $insert + $after;
        return $array;
    }

    /**
     * Получить смещение элемента с определенным индексом от начала массива
     *
     * @param array $array          Просматриваемый массив
     * @param int   $neededIndex    Индекс, чье смещение нас интересует
     *
     * @return bool|int
     */
    public function keyOffset(array $array, $neededIndex)
    {
        reset($array);
        $currentPosition = 0;
        while ($item = each($array))
        {
            if ($item['key'] == $neededIndex)
            {
                return $currentPosition;
            }
            $currentPosition++;
        }
        return false;
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
	 * @return array результат.
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
                if (!isset($array[$key])) {
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
	 * Упорядочивание списка для вывода дерева по полю parentId
	 *
	 * @param array $collection
	 * @param boolean $include_unparented Оставить элементы без предка.
	 * Если false, элементы будут исключены из списка.
	 * @param strign $keyField
     * @param string $parentField
	 * @return array
	 */
	public function sortByParent($collection, $includeUnparented = false,
        $keyField = 'id', $parentField = 'parentId')
	{
		$list = $collection;
		if (empty($list)) {
			return $collection;
		}
        $keyField = $keyField ?: 'id';
        $parentField = $parentField ?: 'parentId';
		$firstIds = $this->column($collection, $keyField);
		$parents = array();
		$childOf = 0;
		$result = array();
		$i = 0;
		$index = array(0 => 0);
		$fullIndex = array(-1 => '');
		do {
			$finish = true;
			for ($i = 0; $i < count($list); ++$i) {
				if ($list[$i][$parentField] != $childOf) {
                    continue;
                }
                if (!isset($index[count($parents)])) {
                    $index[count($parents)] = 1;
                } else {
                    $index[count($parents)]++;
                }
                $n = count($result);
                $result[$n] = $list[$i];
                $result[$n]['data']['level'] = count($parents);
                $result[$n]['data']['index'] = $index[count($parents)];
                $parentsCount = count($parents);
                if ($parentsCount > 0) {
                    $fullIndex = $fullIndex[$parentsCount - 1] .
                        $index[count($parents)];
                } else {
                    $fullIndex = (string) $index[count($parents)];
                }
                $result[$n]['data']['fullIndex'] = $fullIndex;
                $result[$n]['data']['brokenParent'] = false;
                $fullIndex[$parentsCount] = $fullIndex . '.';
                array_push($parents, $childOf);
                $childOf = $list[$i][$keyField];
                for ($j = $i; $j < count($list) - 1; $j++) {
                    $list[$j] = $list[$j + 1];
                }
                array_pop($list);
                $finish = false;
                break;
			}
			// Элементы с неверно указанным предком
			if ($finish && count($parents) > 0) {
				$index[count($parents)] = 0;
				$childOf = array_pop($parents);
				$finish = false;
			}
		} while (!$finish);
		/**
		 * чтобы не портить сортировку, если таковая есть у
		 * коллекции, с использованием элементов без родителей
		 *
		 * сортируем по level 0, докидываем дочерних
		 */
		if ($includeUnparented) {
			//out досортированный
			$newResult = array();
			//без родителей, неотсортированные
			$listIds = array();
			//отсортированные родители: level = 0
			$resultIds = array();
			//отсортированные дочерние: level > 0
			$resultSubIds = array();
			for ($i = 0; $i < count($list); $i++) {
				$listIds[$list[$i][$keyField]] = $i;
			}
            $parentId = 0;
			for ($i = 0; $i < count($result); $i++) {
				if (!$result[$i][$parentField]) {
					$parentId = $result[$i][$keyField];
					$resultIds[$result[$i][$keyField]] = $i;
				} else {
					$resultSubIds[$parentId][$result[$i][$keyField]] = $i;
				}
			}
			for ($i = 0; $i < count($firstIds); $i++) {
				if (isset($resultIds[$firstIds[$i]])) {
					$newResult[] = $result[$resultIds[$firstIds[$i]]];
					if (isset($resultSubIds[$firstIds[$i]])) {
						foreach ($resultSubIds[$firstIds[$i]] as $index) {
							$newResult[] = $result[$index];
						}
					}
				} elseif (isset($listIds[$firstIds[$i]])) {
					$newResult[] = $list[$listIds[$firstIds[$i]]];
				}
			}
			$result = $newResult;
		}
		return $result;
	}
    
    
    /**
     * Построить дерево
     * @param type $array
     * @param type $parentField
     * @return type
     */
    public function sortArrayByParent($array, $parentField = 'parentId') 
    {
        $arrayReindexed = array();
        foreach ($array as $item) {
            $arrayReindexed[$item['id']] = $item;
        }
        $arrayPrepared = array();
        foreach ($arrayReindexed as $key => $item) {
            $parentId = $item[$parentField];
            $parents = array();
            while($parentId) {
                if (!isset($arrayReindexed[$parentId])) {
                    break;
                }
                $parent = $arrayReindexed[$parentId];
                $parents[] = $parent;
                $parentId = $parent[$parentField];
            }
            $arrayPrepared[$key] = $item;
            $arrayPrepared[$key]['parents'] = $parents;
        }
        $arrayGroupsByLevel = array();
        foreach ($arrayPrepared as $item) {
            $level = count($item['parents']);
            $arrayGroupsByLevel[$level][] = $item;
        }
        $arrayGroupsByLevelReversed = array_reverse($arrayGroupsByLevel);
        foreach ($arrayGroupsByLevelReversed as $key => $group) {
            foreach ($group as $key => $item) {
                if (!$item[$parentField]) {
                    continue;
                }
                $arrayReindexed[$item[$parentField]]['children'][] = 
                    $arrayReindexed[$item['id']];
            }
        }
        $arrayResult = array();
        foreach ($arrayReindexed as $item) {
            if ($item[$parentField]) {
                continue;
            }
            $arrayResult[] = $item;
        }
        return $arrayResult;
    }
    
    /**
     * Рекурсивный поиск узла в дереве
     * @param array $tree дерево
     * @param string $nodeName имя узла
     * @return array узел
     */
    public function findNodeInTreeRecursive($tree, $nodeValue, $nodeName = 'id', $childrenName = 'children')
    {
        foreach ($tree as $node) {
            if ($node[$nodeName] == $nodeValue) {
                if (isset($node[$childrenName])) {
                    return $node[$childrenName];
                } else {
                    return null;
                }
            } elseif (isset($node[$childrenName])) {
                $neededNode = $this->findNodeInTreeRecursive($node[$childrenName], $nodeValue, $nodeName, $childrenName);
                if ($neededNode) {
                    return $neededNode;
                }
            }
        }
        return null;
    }
    
    public function treeToFlatArray($tree, $childrenName = 'children') 
    {
        $noChilds = 0;
        foreach($tree as $key => $node) {
            if (isset($node[$childrenName])) {
                
                $nodeRemoved = $node[$childrenName];
                unset($tree[$key][$node[$childrenName]]);
                array_push($tree, $nodeRemoved); 
                $noChilds =1;
                $this->treeToFlatArray($tree);
            } 
        }
        if($noChilds == 0) {
            return $tree;
        }
    }
    
    /**
     * 
     * @param array $array многомерный массив
     * @param boolean $keys включать ли в этоговой массив ключи
     * @return \RecursiveIteratorIterator
     */
    public function flattenArray($array, $keys = false) 
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($array)
        );
        $flatArray = [];
        foreach($iterator as $key => $value) {
            $flatArray[] = $value;
            if ($keys) {
                $flatArray[] = $key;
            }
        }
        return $flatArray;
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

    /**
     * Получить смещение определенного элемента от начала массива
     *
     * @param array $array          Просматриваемый массив
     * @param mixed $neededValue    Элемент, чье смещение нас интересует
     *
     * @return bool|int
     */
    public function valueOffset(array $array, $neededValue)
    {
        reset($array);
        $currentPosition = 0;
        while ($item = each($array))
        {
            if ($item['value'] == $neededValue)
            {
                return $currentPosition;
            }
            $currentPosition++;
        }
        return false;
    }
}