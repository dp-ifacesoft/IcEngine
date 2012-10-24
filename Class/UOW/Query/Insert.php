<?php

/**
 * По сути mysql всё
 */

class UOW_Query_Insert extends UOW_Query_Abstract
{
	/**
	 * @inheritdoc
	 *
	 * @param string $key
	 * @param array $data
	 */
	public function build($key, $data)
	{
		$dataValues = $data[0]['values'];
		$part = explode('@', $key);
		$table = $part[0];
		$fields = array_keys($dataValues);
		/**
		 * Пока так
		 */
		$values = array();
		foreach ($data as $key=>$dataValues) {
			foreach ($dataValues['values'] as $value) {
				$values[$key][] = '"' . mysql_escape_string($value) . '"';
			}
		}
		$valuesQuery = array();
		foreach ($values as $v) {
			$valuesQuery[] = '(' . implode(',', $v) . ')';
		}
		$table = 'ice_' . strtolower($table);
		$query = 'INSERT INTO ' . $table . ' ';
		$query .= '(' . implode(',', $fields) . ') ';
		$query .= 'VALUES' . implode(',', $valuesQuery);
		return $query;
	}

	private function prepare()
	{

	}

	/**
	 * @inheritdoc
	 * @param Query_Abstract $query
	 */
	public function push(Query_Abstract $query)
	{
		$table = $query->getPart('INSERT');
		$tableScheme = Config_Manager::get('Model_Mapper_' . $table);
		$tableFields = array_keys($tableScheme->fields->asArray());
		$values = $query->getPart('VALUES');
		$resultFields = array();
		foreach ($values as $key=>$value) {
			if (in_array($key, $tableFields)) {
				$resultFields[$key] = $value;
			}
		}
		$dataFields = array_keys($resultFields);
		$uniqName = $table . '@' . md5(implode('', $dataFields));
		UOW::pushRaw('INSERT', $uniqName, array(
			'values'	=> $resultFields
		));
	}
}