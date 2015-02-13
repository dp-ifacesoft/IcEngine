<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для изменения внутреннего контекста вызова контроллера
 * 
 * @author morph
 */
class ControllerManagerDelegeeContext extends ControllerManagerDelegeeAbstract
{   
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $params = $context->getArgs();
        $actionScheme = $scheme->getMethod($context->getAction());
        $controllerManager = $context->getControllerManager();
        if (!empty($actionScheme['Context'])) {
            $actionContext = $controllerManager->serviceInjector()->inject(
                null, $actionScheme['Context']
            );
            $defaultContext = clone $params['context'];
            $params['context'] = isset($params['context'])
                ? $defaultContext->merge($actionContext)
                : $actionContext;
            $context->setArgs($params);
        }
    }
}