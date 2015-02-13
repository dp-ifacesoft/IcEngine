<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для установки нового рендера 
 * 
 * @author morph
 */
class ControllerManagerDelegeeViewRender extends 
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
        if (!empty($actionScheme['ViewRender'])) {
            $controllerManager = $context->getControllerManager();
            $viewRenderManager = $controllerManager->getService(
                'viewRenderManager'
            );
            $viewRenderName = reset($actionScheme['ViewRender']);
            $viewRender = $viewRenderManager->byName(reset($viewRenderName));
            $controller->getTask()->setViewRender($viewRender);
        }
    }
}