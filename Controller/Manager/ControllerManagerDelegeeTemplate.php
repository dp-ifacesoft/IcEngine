<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат по смене шаблона контроллера
 *
 * @author morph
 */
class ControllerManagerDelegeeTemplate extends ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $controllerManager = $context->getControllerManager();
        $scheme = $controllerManager->annotationManager()
            ->getAnnotation($controller);
        $actionScheme = $scheme->getMethod($context->getAction());
        if (!empty($actionScheme['Template'])) {
            $context->getTask()->setTemplate(
                reset($actionScheme['Template']) 
            );
        }
    }
}