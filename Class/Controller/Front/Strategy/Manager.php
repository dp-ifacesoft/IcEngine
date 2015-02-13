<?php

/**
 * Менеджер стратегий front-контроллера
 *
 * @author morph
 */
class Controller_Front_Strategy_Manager
{
    /**
     * Получить стратегию по имени
     * 
     * @param string $name
     * @return Controller_Front_Staregory_Abstract
     */
    public function get($name)
    {
        $className = 'Controller_Front_Strategy_' . $name;
        return new $className;
    }
}