<?php

/**
 * Абстрактный сервис
 *
 * @author markov
 */
class Service_Abstract
{
    /** 
     * @var Service_Locator
     */
    protected static $serviceLocator = null;

    /**
     * Конфиг хелпера
     *
     * @var array
     */
    protected $config = array();
    
    /**
     * Загружает и возвращает конфиг для сервиса
     *
     * @return Objective
     */
    public function config()
    {
        if (is_array($this->config)) {
            /** @var Config_Manager $configManager */
            $configManager = $this->getService('configManager');
            $this->config = $configManager->get(
                get_class($this), $this->config
            );
        }
        return $this->config;
    }
    
    /**
     * @param string $name
     * @return mixed
     */
    public function getService($name)
    {
        $serviceLocator = IcEngine::serviceLocator();
        return $serviceLocator->getService($name);
    }

    /**
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        if (!self::$serviceLocator) {
            self::$serviceLocator = IcEngine::serviceLocator();
        }
        return self::$serviceLocator;
    }

    /**
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }

}