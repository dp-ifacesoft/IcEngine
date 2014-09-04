<?php

/**
 * Менеджер модификаторов состояния вызова действия контроллера
 * 
 * @author morph
 * @Service("controllerActionModifierManager")
 */
class Controller_Action_Modifier_Manager
{
    /**
     * Получить модификатор по имени
     * 
     * @param string $name
     * @return Controller_Action_Modifier_Abstract
     */
    public function get($name)
    {
        $className = 'Controller_Action_Modifier_' . $name;
        return new $className;
    }
}