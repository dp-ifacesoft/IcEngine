<?php

/**
 * @desc Транслятор запросов типа insert драйвера mysql
 * @author goorus, morph
 */
class Query_Translator_Mysql_Insert extends Query_Translator_Mysql_Select
{
	/**
	 * Рендеринг INSERT запроса.
	 *
	 * @param Query_Abstract $query Запрос.
	 * @return string Сформированный SQL запрос.
	 */
	public function _renderInsert (Query_Abstract $query)
	{
		if ($query->getMultiple()) {
			return $this->_renderInsertMultiple($query);
		}
		$table = $query->part (Query::INSERT);
		$sql = 'INSERT ' . strtolower (Model_Scheme::table ($table)) . ' (';
		$fields = array_keys ($query->part (Query::VALUES));
		$values = array_values ($query->part (Query::VALUES));

		for ($i = 0, $icount = count ($fields); $i < $icount; $i++)
		{
			$fields [$i] = self::_escape ($fields [$i]);
			$values [$i] = self::_quote ($values [$i]);
		}

		$fields = implode (', ', $fields);
		$values = implode (', ', $values);

		return $sql . $fields . ') VALUES (' . $values . ')';
	}

	/**
	 * Трансляция множественного INSERT
	 *
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderInsertMultiple(Query_Abstract $query)
	{
		$table = $query->part(Query::INSERT);
		$sql = 'INSERT ' . strtolower(Model_Scheme::table($table)) . ' (';
		$fields = null;
		$values = array();
		$queryValues = $query->part(QUERY::VALUES);
		foreach ($queryValues as $queryValue) {
			if (!$fields) {
				$fields = array_keys($queryValue);
			}
			$values[] = array_values($queryValue);
		}
		foreach ($fields as $key=>$field) {
			$fields[$key] = self::_escape($field);
		}
		$valuesPrepared = array();
		foreach ($values as $key=>$value) {
			foreach ($value as $k=>$v) {
				$values[$key][$k] = self::_quote($v);
			}
			$valuesPrepared[] = implode(', ', $values[$key]);
		}
		$fields = implode(', ', $fields);
		$values = implode('), (', $valuesPrepared);
		$sql = $sql . $fields . ') VALUES (' . $values . ')';

		if (($onDuplicateKey = $query->getFlag('onDuplicateKey'))) {
			$duplicateArray = array();
			foreach ($onDuplicateKey as $field) {
				$duplicateArray[] = $field . ' = VALUES(' . $field . ')';
			}
			$sql .= ' ON DUPLICATE KEY UPDATE ' .
				implode(', ', $duplicateArray);
		}
		return $sql;
	}
}