<?php

/**
 * Обновление single/multiple
 */
class UOW_Query_Update extends UOW_Query_Abstract
{
	private function _multiple($table, $data)
	{
		$fields = array_keys($data[0]['values']);
		$clearFields = array();
		foreach ($fields as $field) {
			if ($field != 'id') {
				$clearFields[] = $field;
			}
		}
		$valueSet = array();
		foreach ($data as $dataValues) {
			$tmp = array_values($dataValues['values']);
			$tmpArray = array();
			foreach ($tmp as $v) {
				$tmpArray[] = '"' . mysql_escape_string($v) . '"';
			}
			$valueSet[] = implode(',', $tmpArray);

		}
		$duplicateArray = array();
		foreach ($clearFields as $clearField) {
			$duplicateArray[] = $clearField . ' = VALUES(' . $clearField . ')';
		}
		$query = 'INSERT INTO ' . 'ice_' . strtolower($table) .
			' (' . implode(',', $fields) . ')' .
			' VALUES(' . implode('), (', $valueSet) . ')' .
			' ON DUPLICATE KEY UPDATE ' .
				implode(',', $duplicateArray);
		return $query;
	}

	private function _single($table, $data)
	{
		$values = $data[0]['values'];
		reset($values);
		$array = each($values);
		$field = $array[0];
		$value = $array[1];
		$keys = array();
		foreach ($data as $where) {
			if (!isset($where['wheres']['id'])) {
				continue;
			}
			$keys[] = $where['wheres']['id'];
		}
		if (!$keys) {
			return;
		}
		$query = 'UPDATE ' . 'ice_' . strtolower($table) .
			' SET ' . $field . ' = "' . mysql_escape_string($value) . '"' .
			' WHERE id IN(' . implode(',', $keys) . ')';
		return $query;
	}

	/**
	 * @inheritdoc
	 * @param string $key
	 * @param array $data
	 */
	public function build($key, $data)
	{
		$parts = explode('@', $key);
		$table = $parts[0];
		$function = $this->getType(count($parts));
		$query = call_user_func_array(array($this, $function), array(
			'table'	=> $table,
			'data'	=> $data
		));
		return $query;
	}

	/**
	 * Тип составления запроса
	 *
	 * @param int $value
	 * @return string
	 */
	public function getType($value)
	{
		if ($value == 2) {
			return '_multiple';
		} elseif ($value == 4) {
			return '_single';
		}
	}

	/**
	 * @inheritdoc
	 * @param Query_Abstract $query
	 */
	public function push(Query_Abstract $query)
	{
		$table = $query->getPart('UPDATE');
		$where = $query->getPart('WHERE')?: false;
		$tableScheme = Config_Manager::get('Model_Mapper_' . $table);
		$tableFields = array_keys($tableScheme->fields->asArray());
		$values = $query->getPart('VALUES')?: false;
		$resultFields = array();
		foreach ($values as $key=>$value) {
			if (in_array($key, $tableFields)) {
				$resultFields[$key] = $value;
			}
		}
		$dataFields = array_keys($resultFields);
		$valuesQ = $values ? '@' . md5(
				implode('', array_values($values))
			) : '';
		$wheres = array();
		if ($where) {
			foreach ($where as $value) {
				$wheres[$value['WHERE']] = $value['VALUE'];
			}
		}
		$whereQ = $wheres ? '@' . md5(
				implode('', array_keys($wheres))
			) : '';
		$uniqName = $table . '@' . md5(implode('', $dataFields)) .
			($whereQ ? $valuesQ . $whereQ : '');
		UOW::pushRaw('UPDATE', $uniqName, array(
			'values'	=> $resultFields,
			'wheres'	=> $wheres
		));
	}
}