<?php

/**
 * Удаление модели из связи моделей "многие-ко-многим"
 * 
 * @author morph
 */
class Model_Mapper_Scheme_Resource_ManyToMany_Link_Delete extends 
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
		if (!$exists) {
			return;
		}
        $toDeleteModel = $model2;
		if ($model2->modelName() != $reference->getModel()) {
			$toDeleteModel = $model1;
		} 
        $itemExists = false;
        $items = $this->resource->items();
        foreach ($items as $i => $item) {
            if ($item[$keyField] == $toDeleteModel->key()) {
                unset($items[$i]);
                $itemExists = true;
                break;
            }
        }
        if ($itemExists) {
            $this->resource->setItems($items);
            $deleteQuery = $queryBuilder
                ->delete()
                ->from($modelName)
                ->where($fromField, $model1->key())
                ->where($toField, $model2->key());
            $dds->execute($deleteQuery);
        }
	}
} 