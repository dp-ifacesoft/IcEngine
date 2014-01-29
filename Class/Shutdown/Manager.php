<?php

/**
 * Менеджер сервисов, которые будут выполнены по завершению приложения
 * 
 * @author morph
 * @Service("shutdownManager")
 */
class Shutdown_Manager extends Manager_Abstract
{
    /**
     * Зарегистрированные сервисы
     * 
     * @var array
     * @Generator
     */
    protected $services = array();
    
    /**
     * Выполнить зарегистрированные сервисы
     */
    public function process()
    {
        foreach ($this->services as $serviceMethod) {
            list($serviceName, $method) = explode('.', $serviceMethod);
            $service = $this->getService($serviceName);
            $service->$method();
        }
    }
    
    /**
     * Зарегистрировать сервис и его метод
     * 
     * @param string $serviceMethod
     */
    public function registerService($serviceMethod)
    {
        $this->services[] = $serviceMethod;
    }
    
    /**
     * Getter for "services"
     *
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }
        
    /**
     * Setter for "services"
     *
     * @param array services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }
    
}