<?php

/**
 * Помощник коллекции моделей
 */
class Helper_Model_Collection
{
    /**
     * Восстановить данные коллекции
     *
     * @param Model_Collection $collection
     */
    public function restoreData($collection)
    {
        $keyField = $collection->keyField();
        if (!$collection->data('collectionData')) {
            return;
        }
        $collectionData = $collection->data('collectionData');
        $fieldsFromData = array();
        if ($collection->data('fieldsFromData')) {
            $fieldsFromData = $collection->data('fieldsFromData');
        }
        foreach ($collection as $item) {
            if (!isset($collectionData[$item[$keyField]])) {
                continue;
            }
            $currentData = $collectionData[$item[$keyField]];
            foreach ($currentData as $fieldName => $fieldValue) {
                if (in_array($fieldName, $fieldsFromData)) {
                    $item[$fieldName] = $fieldValue;
                    if (!in_array($fieldName, $collection->rawFields())) {
                        $collection->rawFields()[] = $fieldName;
                    }
                } else {
                    $item['data'] = array_merge((array) $item['data'], array(
                        $fieldName  => $fieldValue
                    ));
                }
            }
        }
    }
}