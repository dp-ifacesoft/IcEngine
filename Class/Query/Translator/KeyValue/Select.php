<?php

/**
 * Транслятор для key-value хранилищь
 * 
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Select extends Query_Translator_Abstract
{
	/**
	 * Разделитель таблицы и индкса
     * 
	 * @var string
	 */
	public $tableIndexDelim = '_';

	/**
	 * Разделитель индекса и первичного ключа
     * 
	 * @var string
	 */
	public $indexKeyDelim = '/';

	/**
	 * Разделитель значений индексов
     * 
	 * @var string
	 */
	public $valuesDelim = ':';
    
    /**
	 * Возвращает массив масок для выбора ключей.
	 * 
     * @param Query_Abstract $query
	 * @return array
	 * 		Маски ключий для выбора.
	 */
	public function doRenderSelect(Query_Abstract $query)
	{
		return $this->compileKeyMask(
			$this->extractTable($query),
			$query->part(Query::WHERE)
		);
	}

	/**
	 * Возвращает массив масок ключей
	 *
	 * @param string $table Таблица (модель).
	 * @param array $where Часть запроса Query::WHERE
	 * @return array Массив масок
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
		// Запоминаем значения для полей индекса
		foreach ($where as $i => &$part){
			$condition = $part[Query::WHERE];
			if (!is_scalar($part[Query::VALUE])) {
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
			$whereValue = urlencode($part[Query::VALUE]);
			if (!$isLike && $conditionPrepared == $keyField) {
				return array(
					$table . $this->tableIndexDelim .
					'k' . $this->indexKeyDelim .
					$whereValue
				);
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
		// Выбираем наиболее покрывающий индекс.
		$bestValue = 0;
		$bestIndex = 0;
		foreach ($indexesUsed as $i => $index) {
			$usePosition = strpos($index, '1');
			if ($usePosition !== false) {
				// Индекс полностью покрывает запрос
				return array ($this->pattern(
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
	 * Формирование ключей для записи
     * 
	 * @param string $table
	 * @param array $values
	 * @return array
	 */
	public function compileKeys($table, array $values = array())
	{
        $modelScheme = $this->modelScheme();
		$keyField = $modelScheme->keyField($table); 
		if (!isset($values[$keyField])) {
			throw new Exception("Primary key must be defined.");
		}
		$keys = array (
			$table . $this->tableIndexDelim .
			'k' . $this->indexKeyDelim .
			$values[$keyField]
		);
		$indexes = $modelScheme->indexes($table);
		foreach ($indexes as $i => $index) {
			$index = (array) $index;
			$currentValues = array();
			foreach ($index as $name) {
				$currentValues[] = isset($values[$name])
                    ? urlencode($values[$name]) : null;
			}
			$currentValues[] = urlencode($values[$keyField]);
			$keys[] = $table . $this->tableIndexDelim .
				$i . $this->indexKeyDelim .
				implode($this->valuesDelim, $currentValues);
		}
		return $keys;
	}

	/**
	 * Извлекает имя таблицы из запроса
     * 
	 * @param Query_Abstract $query
	 * @return string
	 * @throws Exception
	 */
	public function extractTable(Query_Abstract $query)
	{
		$tables = $query->part(Query::FROM);
		// Отдельно хранятся таблицы для INSERT и UPDATE
		$type = $query->type();
		if (in_array($type, array(Query::INSERT, Query::UPDATE))) {
			return $query->part($type);
		}
		// Иначе SELECT или DELETE
		if (count($tables) != 1) {
			throw new Exception('Invalid query.');
		}
		$table = reset($tables);
		return $table[Query::TABLE];
	}

	/**
	 * Извлекает первичный ключ записи из ключа кэша.
	 * 
     * @param string $key
	 * @return string
	 */
	public function extractId($key)
	{
		$id = substr(strrchr($key, $this->valuesDelim), 1);
		if (!$id) {
			$id = substr(strrchr($key, $this->indexKeyDelim), 1);
		}
		return $id;
	}

	/**
	 * Сформировать паттерн для выборки по ключу
     * 
	 * @param string $table
	 * @param integer $indexPosition
	 * @param string $indexesUsed
	 * @param array $values
	 * @return string
	 */
	public function pattern($table, $indexPosition, $indexesUsed, 
        array $values)
	{
		$pattern = $table . $this->tableIndexDelim . $indexPosition . 
            $this->indexKeyDelim;
		for ($i = 0, $length = strlen($indexesUsed); $i < $length; $i++) {
			if (array_key_exists($i, $values)) {
				$pattern .= $values[$i];
			} else {
				$pattern .= '*';
			}
			$pattern .= $this->valuesDelim;
		}
		return rtrim($pattern, ':') . '*';
	}
}