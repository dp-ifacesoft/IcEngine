<?php
/**
 * Примесь сервис-локатора
 *
 * @author LiverEnemy
 */

trait Trait_Service_Locator
{
    /**
     * @param string $serviceName Имя требуемого сервиса
     *
     * @return mixed
     */
    protected function getService($serviceName)
    {
        $locator = IcEngine::serviceLocator();
        return $locator->getService($serviceName);
    }
} 