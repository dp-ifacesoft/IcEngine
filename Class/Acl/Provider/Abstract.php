<?php

/**
 * Абстрактный провайдер acl
 * 
 * @author morph
 * @ServiceAccessor
 */
abstract class Acl_Provider_Abstract
{
    /**
     * Получить данные для модели
     * 
     * @param mixed $model
     * @return array
     */
    abstract public function forModel($model);
    
    /**
     * Get service by name
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
}