<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат по смене шаблона стратегии
 *
 * @author morph
 */
class ControllerManagerDelegeeLayout extends ControllerManagerDelegeeAbstract
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
        if (!empty($actionScheme['Layout'])) {
            \IcEngine::getTask()->setTemplate(
                reset($actionScheme['Layout'][0]) 
            );
        }
    }
}