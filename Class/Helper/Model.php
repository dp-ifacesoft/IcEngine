<?php

/**
 * Помощник модели
 *
 * @author morph, neon
 * @Service("helperModel")
 */
class Helper_Model
{
    /**
     * Накинуть на модель after опшены
     *
     * @param Model $model
     * @param array $options
     * @return Model
     */
    public function appendAfterOptions($model, $options)
    {
        $locator = IcEngine::serviceLocator();
        $collectionManager = $locator->getService('collectionManager');
        $modelCollection = $collectionManager->create($model->modelName());
        $modelCollection->reset();
        $modelCollection->add($model);
        $optionManager = $locator->getService('collectionOptionManager');
        $optionManager->executeAfter($modelCollection, $options);
        return $modelCollection->first();
    }

    /**
     * Получить public поля подели
     *
     * @param Model $model
     * @return array
     */
    public function getVars($model)
    {
        return get_object_vars($model);
    }
}