<?php

/**
 * Транслятор запросов типа delete для key-value хранилищ
 *
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Delete extends Query_Translator_KeyValue_Select
{
    /**
	 * @inheritdoc
	 */
	public function compileKeyMask($table, array $where)
	{
        $modelScheme = $this->modelScheme();
		$keyField = $modelScheme->keyField($table);
		$indexes = $modelScheme->indexes($table);
		// Покрытие индексом запроса
		// Изначально строка "11111", по мере использования,
		// 1 заменяются на 0. Если индекс покрывает запрос, в конце
		// значение будет равно "000000" == 0
		$indexesUsed = array();
		// Значения для полей индекса
		$indexesValues = array();
		// Отсекаем индексы, которые заведомо не покрывают запрос (короткие)
		// и инициализируем массивы
		foreach ($indexes as $i => $index) {
			if (count($index) < count($where)) {
				unset($indexes[$i]);
			} else {
				$indexesUsed[$i] = str_repeat('1', count($index));
				$indexesValues[$i] = array();
			}
		}
        $resultByKey = array();
		// Запоминаем значения для полей индекса
		foreach ($where as $i => &$part){
			$condition = $part[Query::WHERE];
            $queryValues = is_array($part[Query::VALUE]) ? 
                $part[Query::VALUE] : array($part[Query::VALUE]); 
            if (!$queryValues) {
                return array();
            }
            foreach ($queryValues as $queryValue) {
                if (!is_scalar($queryValue)) {
                    throw new Exception('Condition unsupported.');
                }
                if (!is_array($condition)) {
                    // Получаем таблицу и колонку
                    $condition = explode('.', $condition, 2);
                }
                if (empty($condition)) {
                    throw new Exception('Condition field unsupported.');
                }
                $conditionPrepared = trim(end($condition), '`?= ');
                $isLike = (strtoupper(substr($conditionPrepared, -4, 4)) == 'LIKE');
                $whereValue = urlencode($queryValue);
                if (!$isLike && $conditionPrepared == $keyField) {
                    $resultByKey[] = $table . $this->tableIndexDelim .
                        'k' . $this->indexKeyDelim . $whereValue;
                }
                foreach ($indexes as $j => &$indexParts) {
                    foreach ($indexParts as $k => &$indexPart) {
                        if ($conditionPrepared != $indexPart) {
                            continue;
                        }
                        $indexesUsed[$j][$k] = 0;
                        if ($isLike) {
                            $indexesValues[$j][$k] = str_replace(
                                '%25', '*', $whereValue
                            );
                        } else {
                            $indexesValues[$j][$k] = $whereValue;
                        }
                    }
                    unset($indexPart);
                }
                unset($indexParts);
            }
		}
        if ($resultByKey) {
            return $resultByKey;
        }        
		// Выбираем наиболее покрывающий индекс.
		$bestValue = 0;
		$bestIndex = 0;
		foreach ($indexesUsed as $i => $index) {
			$usePosition = strpos($index, '1');
			if ($usePosition !== false) {
				// Индекс полностью покрывает запрос
				return array($this->pattern(
					$table, $i, $index, $indexesValues[$i]
				));
			}
			if ($usePosition > $bestValue) {
				$bestValue = $usePosition;
				$bestIndex = $i;
			}
		}
		if ($bestValue >= 0) {
			return array($this->pattern(
				$table, $bestIndex,
				$indexesUsed[$bestIndex], $indexesValues[$bestIndex]
			));
		}
		return array();
	}
    
    
	/**
	 * Возвращает массив масок для удаления ключей.
	 *
	 * @param Query_Abstract $query
	 * @return array Массив ключей к удалению.
	 */
	public function doRenderDelete(Query_Abstract $query)
	{
        $table = $this->extractTable($query);
        $where = $query->part(Query::WHERE) ?: array();
		return $this->compileKeyMask($table, $where);
	}
}