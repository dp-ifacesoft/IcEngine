<?php

/**
 * Хелпер для работы с событиями модели
 * 
 * @author morph
 * @Service("helperModelEvent")
 */
class Helper_Model_Event extends Helper_Abstract
{
    /**
     * Выполнить сервис по событию
     * 
     * @param type $model
     * @param type $initialModel
     * @param type $fieldName
     * @param type $service
     */
    public function process($model, $initialModel, $fieldName, $service)
    {
        list($serviceName, $methodName) = explode('.', $service);
        $result = $this->getService($serviceName)->$methodName($model);
        $initialModel->set($fieldName, $result);
    }
}