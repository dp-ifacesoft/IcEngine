<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат по фильтрации входных аргументов
 *
 * @author morph
 */
class ControllerManagerDelegeeInputFilter extends 
    ControllerManagerDelegeeAbstract
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
        if (!empty($actionScheme['InputFilter'])) {
            $filterManager = $controllerManager->getService(
                'dataFilterManager'
            );
            $input = $controller->getInput();
            foreach ($actionScheme['InputFilter'] as $actionArgs) {
                foreach ($actionArgs as $argName => $filters) {
                    foreach ($filters as $filterName) {
                        $filter = $filterManager->get($filterName);
                        $arg = $filter->filter($input[$argName]);
                        $input->send($argName, $arg, 0);
                    }
                }
            }
        }
    }
}