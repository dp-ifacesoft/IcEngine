<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для установки дополнельного провайдера для входного транспорта
 * контроллера
 * 
 * @author morph
 */
class ControllerManagerDelegeeInputProvider extends 
    ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call 
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $actionScheme = $scheme->getMethod($context->getAction());
        if (!empty($actionScheme['InputProvider'])) {
            $controllerManager = $context->getControllerManager();
            $dataProviderManager = $controllerManager->getService(
                'dataProviderManager'
            );
            foreach ($actionScheme['InputProvider'] as $providers) {
                foreach ($providers as $provider) {
                    $provider = $dataProviderManager->get($provider);
                    if (!$provider) {
                        continue;
                    }
                    $controller->getInput()->appendProvider($provider);
                }
            }
        }
    }
}