<?php

/**
 * Транслятор запросов типа insert драйвера mysql
 *
 * @author goorus, morph, neon
 */
class Query_Translator_Mysql_Insert extends Query_Translator_Mysql_Select
{
	/**
	 * Рендеринг INSERT запроса.
	 *
	 * @param Query_Abstract $query Запро с.
	 * @return string Сформированный SQL запрос.
	 */
	public function doRenderInsert(Query_Abstract $query)
	{
		if ($query->getMultiple()) {
			return $this->renderInsertMultiple($query);
		}
        $modelScheme = $this->modelScheme();
		$table = $query->part(Query::INSERT);
		$sql = self::SQL_INSERT . ' ' .
            strtolower($modelScheme->table($table)) . ' (';
		$fields = array_keys($query->part(Query::VALUES));
		$values = array_values($query->part(Query::VALUES));
        $helper = $this->helper();
		for ($i = 0, $count = count($fields); $i < $count; $i++) {
			$fields[$i] = $helper->escape($fields[$i]);
			$values[$i] = $helper->quote($values[$i]);
		}
		$resultFields = implode(', ', $fields);
		$resultValues = implode(', ', $values);
		return $sql . $resultFields .
            ') ' . self::SQL_VALUES . ' (' .
            $resultValues . ')';
	}

	/**
	 * Трансляция множественного INSERT
	 *
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function renderInsertMultiple(Query_Abstract $query)
	{
		$table = $query->part(Query::INSERT);
        $modelScheme = $this->modelScheme();
		$sql = self::SQL_INSERT . ' ' .
            strtolower($modelScheme->table($table)) . ' (';
		$fields = null;
		$values = array();
		$queryValues = $query->part(Query::VALUES);
		foreach ($queryValues as $queryValue) {
			if (!$fields) {
				$fields = array_keys($queryValue);
			}
			$values[] = array_values($queryValue);
		}
        $helper = $this->helper();
		foreach ($fields as $key => $field) {
			$fields[$key] = $helper->escape($field);
		}
		$valuesPrepared = array();
		foreach ($values as $key => $value) {
			foreach ($value as $subKey => $subValue) {
				$values[$key][$subKey] = $helper->quote($subValue);
			}
			$valuesPrepared[] = implode(', ', $values[$key]);
		}
		$fieldsImploded = implode(', ', $fields);
		$valuesImploded = implode('), (', $valuesPrepared);
		$sql = $sql . $fieldsImploded . ') ' . self::SQL_VALUES .
            ' (' . $valuesImploded . ')';
		if (($onDuplicateKey = $query->getFlag('onDuplicateKey'))) {
			$duplicateArray = array();
			foreach ($onDuplicateKey as $field) {
				$duplicateArray[] = '`' .
                    $field . '` = ' . self::SQL_VALUES. '(`' . $field . '`)';
			}
			$sql .= ' ON DUPLICATE KEY UPDATE ' .
				implode(', ', $duplicateArray);
		}
		return $sql;
	}
}