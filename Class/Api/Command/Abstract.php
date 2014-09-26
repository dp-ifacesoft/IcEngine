<?php

/**
 * Абстрактная команда api
 *
 * @author markov
 */
abstract class Api_Command_Abstract
{
    /**
     * Схема параметров
     * пример: ['login', 'password', 'utcode'];
     */
    protected $paramsSchema;

    /**
     * Параметры
     */
    protected $params; 
    
    /**
     * @return mixed результат выполнения команды
     */
    abstract public function run();
    
    /**
     * Устанавливает параметры
     */
    public function setParams($jsonParams)
    {
        $params = json_decode(urldecode($jsonParams));
        $this->params = $params;
    }

    /**
     * Если установленна схема параметров, проверяем, все ли данные переданы
     *
     * @return bool
     */
    public function checkParams()
    {
        if (!$this->paramsSchema) {
            return true;
        }
        $check = true;
        $params = array_keys($this->params);
        foreach ($this->paramsSchema as $paramName) {
            if (!in_array($paramName, $params)) {
                $check = false;
                break;
            }
        }
        return $check;
    }

    protected function getService($serviceName)
    {
        return IcEngine::getServiceLocator()->getService($serviceName);
    }
}