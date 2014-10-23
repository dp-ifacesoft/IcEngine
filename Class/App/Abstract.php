<?php

/**
 * Абстрактный метод для статических гетторов
 *
 * @author Apostle
 */
abstract class App_Abstract
{
    /**
     * Магический вызов если по каким-то причинам не собрался какой-то метод в 
     * App
     * @param string $name имя метода
     * @param array $arguments аргументы
     * @return mixed необходимый сервис
     */
    public static function __callStatic($name, $arguments)
    {
        return IcEngine::getServiceLocator()->getService($name);
    }
}
