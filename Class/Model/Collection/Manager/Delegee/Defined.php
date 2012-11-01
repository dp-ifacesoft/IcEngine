<?php

/**
 * Делигат менеджера коллекций для моделей с определенными данными
 *
 * @author morph
 */
class Model_Collection_Manager_Delegee_Defined
{
    /**
     * @inheritdoc
     */
	public static function load(Model_Collection $collection,
        Query_Abstract $query)
	{
		$modelName = $collection->modelName();
		$rows = $modelName::$rows;
		$where = $query->getPart(Query::WHERE);
		$filter = array();
		foreach ($where as $w) {
			$field = rtrim($w[Query::WHERE], '?');
			if (strpos($field, '.') !== false) {
				list(,$plainField) = explode('.', $field, 2);
				$field = trim($plainField, '`');
			}
			$filter[$field] = $w[Query::VALUE];
		}
		$order = $query->getPart(Query::ORDER);
		$sort = array();
		foreach ($order as $o) {
			$sort[] = $o[0];
		}
        $result = $rows;
        if ($filter) {
            $result = Helper_Array::filter($rows, $filter);
        }
        if ($result && $sort) {
            Helper_Array::masort($result, implode(',', $sort));
        }
		return array('items' => $result);
	}
}