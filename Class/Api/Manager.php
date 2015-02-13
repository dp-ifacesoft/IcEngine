<?php

/**
 * Менеджер api
 * 
 * @author morph
 * @Service("apiManager")
 */
class Api_Manager extends Manager_Abstract
{
    /**
     * Менеджер транспорта api
     * 
     * @var Api_Transport_Manager
     * @Inject("apiTransportManager")
     */
    protected $apiTransportManager;
    
    /**
     * Получить схему api по имени
     * 
     * @param string $name
     * @return Api_Scheme_Abstract
     */
    public function get($name)
    {
        $className = 'Api_Scheme_' . $name;
        $scheme = new $className;
        $transport = $this->apiTransportManager->get(
            $scheme->getTransportName()
        );
        $scheme->setTransport($transport);
        return $scheme;
    }
    
    /**
     * Изменить менеджера транспорта api
     * 
     * @param Api_Transport_Abstract $apiTransportManager
     */
    public function setApiTransportManager($apiTransportManager)
    {
        $this->apiTransportManager = $apiTransportManager;
    }
}