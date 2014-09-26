<?php

/**
 * Объект, который внедряет зависимости
 * 
 * @author morph
 * @Service("serviceInjector")
 */
class Service_Injector
{
    /**
     * Получить ижектор по имени
     * 
     * @param string $name
     * @param Service_Locator $serviceLocator
     * @return Service_Injector_Abstract
     */
    public function get($name, $serviceLocator = null)
    {
        $className = 'Service_Injector_' . $name;
        $injector = new $className;
        if ($serviceLocator) {
            $injector->setServiceLocator($serviceLocator);
        }
        return $injector;
    }
}