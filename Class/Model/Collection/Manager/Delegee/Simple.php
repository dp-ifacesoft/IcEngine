<?php

/**
 * Базовый загрузчик для менеджера коллекций
 *
 * @author morph
 */
class Model_Collection_Manager_Delegee_Simple
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
        // Выполняем запрос, получаем элементы коллеции
		$modelName = $collection->modelName();
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $queryResult = $dataSource->execute($query)->getResult();
		$collection->queryResult($queryResult);
		// Если установлен флагp CALC_FOUND_ROWS,
		// то назначаем ему значение
		if ($query->getPart(Query::CALC_FOUND_ROWS)) {
			$collection->data('foundRows', $queryResult->foundRows());
		}
        $scheme = $modelScheme->scheme($modelName);
		if (!$scheme['fields']) {
			return;
		}
		$schemeFields = $scheme['fields']->keys();
		$rows = $queryResult->asTable();
        if (!$rows) {
            return array('items' => array());
        }
        $currentFields = array_keys($rows[0]);
        $needleFields = array_intersect($schemeFields, $currentFields);
        $addictFields = array_diff($currentFields, $schemeFields);
        if ($addictFields) {
            $helperArray = $serviceLocator->getService('helperArray');
            $items = $helperArray->column($rows, $needleFields);
            $addicts = $helperArray->column($rows, $addictFields);
            if (count($addictFields) == 1) {
                $addictField = reset($addictFields);
                foreach ($addicts as $i => $addict) {
                    $addicts[$i] = array($addictField => $addict);
                }
            }
            $collection->data('addicts', $addicts);
        } else {
            $items = $rows;
        }
        $items = $rows;
        return array('items' => $items);
	}
}