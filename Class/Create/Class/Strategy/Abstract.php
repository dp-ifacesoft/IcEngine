<?php

/**
 * Абстрактный класс создания классов
 * 
 * @author apostle
 */
abstract class Create_Class_Strategy_Abstract
{
    /**
     * Создать класс с параметрами
     */
    public function create($params) {
        $method = strtolower(App::helperClass()->getClassType($params['name']));
        App::controllerManager()->call('Create', $method, $params);
    }
}

