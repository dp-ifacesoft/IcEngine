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
		foreach ($data as $loaderName=>$raws) {
			foreach ($raws as $raw) {
				foreach ($raw['wheres'] as $value) {
					$wheres[] = $value;
				}
			}
		}
		list($modelName, $keyField) = explode('@', $key);
		$locator = IcEngine::serviceLocator();
		$queryBuilder = $locator->getService('query');
		$query = $queryBuilder->select('*')
			->from($modelName);
		if (count($wheres) == 1) {
			foreach ($wheres as $where) {
				$query->where($keyField, $where);
			}
		} else {
			$query->where($keyField, $wheres);
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
		$uniqName = $object->modelName() . '@' .
			implode(':', array_keys($wheres));
		$data = array(
			'object'	=> &$object,
			'wheres'	=> $wheres
		);
		$locator = IcEngine::serviceLocator();
		$unitOfWork = $locator->getService('unitOfWork');
		$unitOfWork->pushRaw(QUERY::SELECT, $uniqName, $data, $loaderName);
	}
}