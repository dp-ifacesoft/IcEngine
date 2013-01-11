<?php

/**
 * Тип ссылки "один-ко-многим"
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_OneToMany extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
		$field = $this->getField();
        $serviceLocator = IcEngine::serviceLocator();
		if (!$field[0]) {
            $modelScheme = $serviceLocator->getService('modelScheme');
			$field = $modelName . '__' . $modelScheme->keyField($modelName);
		} else {
            $field = reset($field);
        }
        $modelName = $this->getModel();
        $collectionManager = $serviceLocator->getService('collectionManager');
        $queryBuilder = $serviceLocator->getService('query');
        $query = $queryBuilder->where($this->getModel() . '.' . $field, $id);
        $collection = $collectionManager->byQuery($modelName, $query);
		return $this->resource()->setItems($collection);
	}
}