<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для before-вызова
 *
 * @author morph
 */
class ControllerManagerDelegeeBefore extends ControllerManagerDelegeeAbstract
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
        if (!empty($actionScheme['Before'])) {
            $actions = reset($actionScheme['Before']);
            $result = array();
            foreach ($actions as $action) {
                if (strpos($action, '/') !== false) {
                    list($controllerName, $actionName) = explode('/', $action);
                } else {
                    $controllerName = $controller->getName();
                    $actionName = $action;
                }
                $buffer = $context->getControllerManager()->call(
                    $controllerName,
                    $actionName,
                    $controller->getInput()
                )->getTransaction()->buffer();
                if ($buffer) {
                    $result = array_merge($result, $buffer);
                }
            }
            if ($result) {
                $params['before'] = $result;
                $context->setArgs($params);
            }
        }
    }
}