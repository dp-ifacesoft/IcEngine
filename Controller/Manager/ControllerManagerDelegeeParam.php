<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для передачи в транспорт контроллера параментров из метода 
 * класса контроллера
 * 
 * @author morph
 */
class ControllerManagerDelegeeParam extends ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $reflection = new \ReflectionMethod($controller, $context->getAction());
        $params = $reflection->getParameters();
        $currentInput = $controller->getInput();
        $provider = $currentInput->getProvider(0);
        $resultParams = array();
        if (!$params) {
            return array();
        }
        foreach ($params as $param) {
            $value = $currentInput->receive($param->name);
            if (is_null($value) && $param->isOptional()) {
                $value = $param->getDefaultValue();
            }
            if ($provider) {
                $provider->set($param->name, $value);
            }
            $resultParams[$param->name] = $value;
        }
        $context->setArgs(array_merge($resultParams, $context->getArgs()));
    }
}