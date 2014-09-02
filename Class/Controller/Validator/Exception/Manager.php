<?php

/**
 * Менеджер исключений для валидаторов контроллера
 * 
 * @author morph
 * @Service("controllerValidatorExceptionManager")
 */
class Controller_Validator_Exception_Manager extends Manager_Abstract
{
    /**
     * Получить исключение по имени
     * 
     * @param string $name
     * @param array $params
     * @return Controller_Validator_Exception_Abstract
     */
    public function get($name, $params = array())
    {
        $className = 'Controller_Validator_Exception_' . $name;
        throw new $className($params);
    }
}