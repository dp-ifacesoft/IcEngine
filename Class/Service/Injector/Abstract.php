<?php

/**
 * Абстрактный механизм внедрения зависимости
 * 
 * @author morph
 */
abstract class Service_Injector_Abstract
{
    /**
     * Локатор для сервисов
     * 
     * @var Service_Locator
     */
    protected $serviceLocator;
    
    /**
     * Вернуть локатор сервисов
     * 
     * @author morph
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    /**
     * Внедрение зависимости
     * 
     * @param object $object
     * @param scheme $scheme
     */
    abstract public function inject($object, $scheme);

    /**
     * Изменить локатор услуг
     * 
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}