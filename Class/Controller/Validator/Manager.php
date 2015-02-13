<?php

/**
 * Менеджер валидаторов контроллера
 * 
 * @author morph
 * @Service("controllerValidatorManager")
 */
class Controller_Validator_Manager extends Manager_Abstract
{
    /**
     * Получить валидатор контроллеров по имени
     * 
     * @param string $name
     * @param ControllerContext $context
     * @return Controller_Validator_Abstract
     */
    public function get($name, $context)
    {
        $className = 'Controller_Validator_' . $name;
        $validator = new $className($context);
        return $validator;
    }
}