<?php

/**
 * Абстрактный делегат модели Model_Manager'а
 *
 * @author neon
 */
class Model_Manager_Delegee_Abstract
{
    /**
     * Созданные делегаты
     *
     * @var array
     */
    protected $delegees;

    /**
	 * Получение данных модели
     *
	 * @param string $modelName Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $source Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public function get($modelName, $key, $object = null)
	{
        $params = is_array($object) ? $object : array();
        $model = new $modelName($params);
		return $model;
	}

    /**
     * Удаление модели
     *
     * @param Model $model
     */
    public function remove($model)
    {
        $locator = IcEngine::serviceLocator();
        $modelScheme = $locator->getService('modelScheme');
        $modelName = $model->modelName();
        $dataSource = $modelScheme->dataSource($modelName);
        $queryBuilder = $locator->getService('query');
        $query = $queryBuilder
            ->delete()
            ->from($modelName)
            ->where($model->keyField(), $model->key());
        $dataSource->execute($query);
    }

    /**
	 * Сохранение данных модели
     *
	 * @param Model $model Объект модели.
	 * @param boolean $hardInsert Объект будет вставлен в источник данных.
	 */
	public function set($model, $hardInsert = false)
	{
        $locator = IcEngine::serviceLocator();
        $resourceKey = $model->resourceKey();
        $updatedFields = $model->getUpdatedFields();
        if (!$model->key() || $hardInsert) {
            $updatedFields = $model->getFields();
            $model->setUpdatedFields($updatedFields);
        }
        if ($updatedFields) {
            $helperModelManager = $locator->getService('helperModelManager');
            $helperModelManager->write($model, $hardInsert);
        }
        $model->setUpdatedFields(array());
        $resourceManager = $locator->getService('resourceManager');
		$resourceManager->set('Model', $resourceKey, $model);
		$resourceManager->setUpdated('Model', $resourceKey, $updatedFields);
	}
}