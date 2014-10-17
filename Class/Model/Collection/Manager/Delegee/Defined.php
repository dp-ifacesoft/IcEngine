<?php

/**
 * Делигат менеджера коллекций для моделей с определенными данными
 *
 * @author morph
 */
class Model_Collection_Manager_Delegee_Defined
{
    /**
     * Загружает коллекцию
     *
     * @param Model_Collection $collection
     * @param Query_Abstract $query
     * @return array
     */
	public static function load(Model_Collection $collection,
        Query_Abstract $query)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $helperArray = $serviceLocator->getService('helperArray');
		$modelName = $collection->modelName();
		$rows = $modelName::$rows;
        if (!$rows) {
            $config = $serviceLocator->getService('configManager')
                ->get($modelName);
            if ($config && $config->rows) {
                $rows = $config->rows->__toArray();
            }
        }
		$where = $query->getPart(Query::WHERE);
		$filter = array();
		foreach ($where as $currentWhere) {
			$field = rtrim($currentWhere[Query::WHERE], '?');
			if (strpos($field, '.') !== false) {
				list(,$plainField) = explode('.', $field, 2);
				$field = trim($plainField, '`');
			}
			$filter[$field] = $currentWhere[Query::VALUE];
		}
		$order = $query->getPart(Query::ORDER);
		$sort = array();
		foreach ($order as $currentOrder) {
			$sort[] = $currentOrder[0]; 
		}
        $result = $rows;
        if ($filter) {
            $result = $helperArray->filter($rows, $filter);
        }
        if ($result && $sort) {
            $result = $helperArray->masort($result, implode(',', $sort));
        }
        if ($query->getPart(Query::CALC_FOUND_ROWS)) {
			$collection->data('foundRows', count($result));
		}
		return array('items' => $result);
	}
}