<?php

/**
 * Обновление single/multiple
 *
 * @author neon
 */
class Unit_Of_Work_Query_Update extends Unit_Of_Work_Query_Abstract
{
	private function _multiple($modelName, $data)
	{
		$clearFields = null;
		$dataPrepared = array();
		foreach ($data as $v) {
			if (!$clearFields) {
				$clearFields = array_keys($v['values']);
			}
			$dataPrepared[] = array_merge($v['wheres'], $v['values']);
		}
		$locator = IcEngine::serviceLocator();
		$queryBuilder = $locator->getService('query');
		$query = $queryBuilder->insert($modelName);
        $query->setMultiple(true);
        $query->setFlag('onDuplicateKey', array_values($clearFields));
		foreach ($dataPrepared as $values) {
			$query->values($values);
		}/*
        $eventManager = $locator->getService('eventManager');
        $signal = $eventManager->getSignal('Data_Source_Update_Multiply');
        $signal->setData(array(
            'modelName' => $modelName,
            'data'      => $dataPrepared
        ));
        $signal->notify();*/
		return array(
			'modelName'	=> $modelName,
			'query'		=>	$query
		);
	}

	private function _single($modelName, $data)
	{
		$valueField = null;
		$value = null;
		$keyField = null;
		$keys = array();
		foreach ($data as $where) {
			if (!$keyField) {
				$keyField = implode('', array_keys($where['wheres']));
			}
			if (!$valueField) {
				$valueField = implode('', array_keys($where['values']));
				$value = implode('', array_values($where['values']));
			}
			$keys[] = $where['wheres'][$keyField];
		}
		if (!$keys) {
			return;
		}
		$locator = IcEngine::serviceLocator();
		$queryBuilder = $locator->getService('query');
		$query = $queryBuilder->update($modelName)
			->set($valueField, $value)
			->where($keyField, $keys);
		return array(
			'modelName'	=> $modelName,
			'query'		=> $query
		);
	}

	/**
	 * @inheritdoc
	 * @param string $key
	 * @param array $data
	 */
	public function build($key, $data)
	{
		$parts = explode('@', $key);
		$modelName = $parts[0];
		$function = $this->getType(count($parts));
		$query = call_user_func_array(array($this, $function), array(
			'modelName'	=> $modelName,
			'data'		=> $data
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
		if ($value == 3) {
			return '_multiple';
		} elseif ($value == 4) {
			return '_single';
		}
	}

	/**
	 * @inheritdoc
	 * @param Query_Abstract $query
	 */
	public function push(Query_Abstract $query, $object = null, $loaderName = null)
	{
		$locator = IcEngine::serviceLocator();
		$configManager = $locator->getService('configManager');
		$table = $query->getPart(QUERY::UPDATE);
		$where = $query->getPart(QUERY::WHERE)?: false;
		$tableScheme = $configManager->get('Model_Mapper_' . $table);
		$tableFields = array_keys($tableScheme->fields->asArray());
		$values = $query->getPart(QUERY::VALUES)?: false;
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
				$wheres[$value[QUERY::WHERE]] = $value[QUERY::VALUE];
			}
		}
		$whereQ = $wheres ? '@' . md5(
				implode('', array_keys($wheres))
			) : '';
		//multiple
		if (count($dataFields) > 1 && isset($wheres['id'])) {
			$uniqName = $table . '@' .
			implode('', $dataFields) . $whereQ;
		//single
		} else {
			$uniqName = $table . '@' .
			implode('', $dataFields) . $valuesQ . $whereQ;
		}
		$unitOfWork = $locator->getService('unitOfWork');
		$unitOfWork->pushRaw(QUERY::UPDATE, $uniqName, array(
			'values'	=> $resultFields,
			'wheres'	=> $wheres
		));
	}
}