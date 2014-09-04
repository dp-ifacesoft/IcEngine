<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для установки нового входного транспорта контроллера
 * 
 * @author morph
 */
class ControllerManagerDelegeeInputTransport extends 
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
        if (!empty($actionScheme['InputTransport'])) {
            $controllerManager = $context->getControllerManager();
            $dataTransportrManager = $controllerManager->getService(
                'dataTransportManager'
            );
            $transport = $dataTransportrManager->get(
                reset($actionScheme['InputProvider'])
            );
            $controller->setInput($transport);
        }
    }
}