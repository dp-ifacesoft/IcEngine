<?php

/**
 * Менеджер репозиториев для модели
 *
 * @author morph
 * @Service("modelRepositoryManager")
 */
class Model_Repository_Manager extends Manager_Abstract
{
    /**
     * Уже созданные репозитории
     *
     * @var array
     */
    protected $repositories;

    /**
     * Получить репозиторий модели
     *
     * @param Model $model
     * @return Model_Repository
     */
    public function get($model)
    {
        $modelName = $model->modelName();
        if (!isset($this->repositories[$modelName])) {
            $classAnnotation = $this->annotationManager()->getAnnotation(
                $modelName
            )->getData()['class'];
            if (isset($classAnnotation['Repository'])) {
                $className = reset($classAnnotation['Repository'][0]);
            } else {
                $className = $modelName . '_Repository';
            }
            $parts = explode('_', $className);
            $parts[0] = lcfirst($parts[0]);
            $serviceName = implode('', $parts);
            $repositoryService = $this->getService($serviceName);
            if (!$repositoryService) {
                throw new ErrorException(
                    'Repository for model "' . $modelName . '" unexists'
                );
            }
            $repository = $repositoryService->newInstance(array($model));
            $this->repositories[$modelName] = $repository;
        } else {
            $repository = $this->repositories[$modelName];
        }
        $repository->setModel($model);
        return $repository;
    }
}