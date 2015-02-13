<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат по валидации входных аргументов
 *
 * @author morph
 */
class ControllerManagerDelegeeInputValidator extends 
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
        if (!empty($actionScheme['InputValidator'])) {
            $config = $controller->config();
            $dataValidator = $controllerManager->getService('dataValidator');
            $hasScheme = false;
            $scheme = array();
            if (is_array($actionScheme['InputValidator'])) {
                foreach ($actionScheme['InputValidator'] as $data) {
                    if (is_string($data)) {
                        $hasScheme = true;
                        $configScheme = $config['validatorSchemes'][$data] 
                            ? $config['validatorSchemes'][$data]->__toArray()
                            : array();
                        $scheme = array_merge($scheme, $configScheme);
                    } else {
                        $scheme = array_merge($scheme, reset($data));
                    }
                }
            }
            if (!$hasScheme) {
                $configScheme = $config['validatorSchemes']['default'] 
                    ? $config['validatorSchemes']['default']->__toArray()
                    : array();
                $scheme = array_merge($scheme, $configScheme);
            }
            if ($scheme) {
                $input = $controller->getInput();
                $result = $dataValidator->validate($input, $scheme);
                if (is_array($result)) {
                    $controller->sendError(array('validate' => $result));
                    $controller->getTask()->setIgnore(true);
                }
            }
        }
    }
}