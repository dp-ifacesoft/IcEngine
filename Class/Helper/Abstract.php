<?php

/**
 * Абстрактный класс для хелперов
 *
 * @author neon
 */
class Helper_Abstract
{

    /**
     * @var Service_Locator
     */
    protected static $serviceLocator = null;

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