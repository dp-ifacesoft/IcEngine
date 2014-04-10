<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат по валидации входных данных контроллера
 *
 * @author morph
 */
class ControllerManagerDelegeeValidator extends ControllerManagerDelegeeAbstract
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
        if (!empty($actionScheme['Validator'])) {
            $validatorManager = $controllerManager->getService(
                'controllerValidatorManager'
            );
            foreach ($actionScheme['Validator'] as $validators) {
                foreach ($validators as $validator => $params) {
                    if (is_numeric($validator)) {
                        $validator = $params;
                        $params = array();
                    }
                    $validator = $validatorManager->get($validator, $context);
                    if (!$validator->validate($params)) {
                        return;
                    }
                }
            }
        }
    }
}