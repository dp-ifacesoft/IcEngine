<?php

/**
 * Объект переноса данных для добавления новой модели к связи "многие-ко-многим"
 * 
 * @author morph
 */
class Model_Mapper_Scheme_Resource_ManyToMany_Link_Add extends 
    Model_Mapper_Scheme_Resource_Link_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function link($model1, $model2, $reference)
	{
        list($fieldData, $modelName) = $reference->getField();
		$fromField = reset($fieldData);
        $toField = $fieldData['on'];
        $serviceLocator = IcEngine::serviceLocator();
        $queryBuilder = $serviceLocator->getService('queryBuilder');
        $dds = $serviceLocator->getService('dds');
        $keyField = $serviceLocator->getService('modelScheme')->keyField(
            $modelName
        );
		$existsQuery = $queryBuilder
			->select($keyField)
			->from($modelName)
			->where($fromField, $model1->key())
            ->where($toField, $model2->key());
		$exists = $dds->execute($existsQuery)->getResult()->asValue();
		if ($exists) {
			return;
		}
		if ($model2->modelName() != $reference->getModel()) {
			$this->resource->addItem($model1);
		} else {
			$this->resource->addItem($model2);
		}
		$insertQuery = $queryBuilder
			->insert($modelName)
			->values(array(
				$fromField	=> $model1->key(),
				$toField	=> $model2->key()
			));
		$dds->execute($insertQuery);
	}
}