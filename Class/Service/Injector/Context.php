<?php

/**
 * Внедрение зависимости через контекст
 * 
 * @author morph
 */
class Service_Injector_Context extends Service_Injector_Abstract
{
    /**
     * @inheritdoc
     */
    public function inject($object, $scheme)
    {
        $services = array();
        foreach ($scheme as $service) {
            foreach ($service as $serviceName) {
                $fetchedService = $this->serviceLocator->getService(
                    $serviceName
                );
                $services[$serviceName] = $fetchedService;
            }
        }
        $context = new Objective($services);
        return $context;
    }
}