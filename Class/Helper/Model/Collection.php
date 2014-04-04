<?php

/**
 * Помощник коллекции моделей
 *
 * @author morph
 * @Service("helperModelCollection")
 */
class Helper_Model_Collection extends Helper_Abstract
{
    /**
	 * Переназначение коллекции на модель
	 *
	 * @param Model $model
	 * @param Model_Collection $collection
	 * @return void
	 */
	public function rejoin($model, $collection)
	{
		$collection->update(array(
			'table'	=> $model->modelName(),
			'rowId'	=> $model->key()
		));
	}

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

	/**
	 * Упорядочивание списка для вывода дерева по полю parentId
	 *
	 * @param Model_Collection $collection
	 * @param boolean $include_unparented Оставить элементы без предка.
	 * Если false, элементы будут исключены из списка.
	 *
	 * @return Model_Collection
	 */
	public function sortByParent($collection, $includeUnparented = false)
	{
        $items = $this->getService('helperArray')->sortByParent(
            $collection->items(), $includeUnparented, $collection->keyField()
        );
		$collection->setItems($items);
		return $collection;
	}
}