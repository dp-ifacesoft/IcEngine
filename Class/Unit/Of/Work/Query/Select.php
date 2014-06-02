<?php

/**
 * Запросы вида селект
 *
 * @author neon
 */
class Unit_Of_Work_Query_Select extends Unit_Of_Work_Query_Abstract
{
	/**
	 * @inheritdoc
	 * @param string $key
	 * @param array $data
	 */
	public function build($key, $data)
	{
		$wheres = array();
		$loaderName = null;
		list($modelName, $keyField) = explode('@', $key);
		foreach ($data as $loaderName=>$raws) {
			foreach ($raws as $raw) {
				foreach ($raw['wheres'] as $keyValue=>$value) {
					if (strstr($keyValue, '?')) {
						$keyValue = str_replace('?', '"' . $value . '"', $keyValue);
						$value = null;
					}
					$wheres[$keyValue] = $value;
				}
			}
		}
		/*echo '---' . $modelName;
		if ($modelName == 'Activation') {
			print_r($wheres);die;
		}*/
		$query = Query::instance()
			->select('*')
			->from($modelName);
		if (count($wheres) == 1) {
			foreach ($wheres as $where) {
				$query->where($keyField, $where);
			}
		} else {
			foreach ($wheres as $keyQhere=>$where) {
				if (is_null($where)) {
					$query->where($keyQhere);
				} else {
					$query->where($keyQhere, $where);
				}
			}
		}
		return array(
			'modelName'	=> $modelName,
			'query'		=> $query,
			'loader'	=> $loaderName
		);
	}

	/**
	 * @inheritdoc
	 *
	 * @param Query_Abstract $query
	 * @param Model $object
	 */
	public function push(Query_Abstract $query, $object = null, $loaderName = null)
	{
		$wheres = array();
		$where = $query->getPart(QUERY::WHERE);
		if ($where) {
			foreach ($where as $value) {
				$wheres[$value[QUERY::WHERE]] = $value[QUERY::VALUE];
			}
		}

		$wheresPrepared = array();
		foreach ($wheres as $key=>$value) {
			$fieldName = trim(strtr($key, array(
				'?'	=> '',
				'<'	=> '',
				'>'	=> ''
			)));
			$wheresPrepared[$fieldName] = $value;
		}
		$uniqName = $object->modelName() . '@' .
			implode(':', array_keys($wheresPrepared));
		$data = array(
			'object'	=> &$object,
			'wheres'	=> $wheres
		);
		Unit_Of_Work::pushRaw(QUERY::SELECT, $uniqName, $data, $loaderName);
	}
}