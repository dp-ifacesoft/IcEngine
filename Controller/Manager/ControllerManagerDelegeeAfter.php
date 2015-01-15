<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для after-вызова
 * 
 * @author morph
 */
class ControllerManagerDelegeeAfter extends ControllerManagerDelegeeAbstract
{ 
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $actionScheme = $scheme->getMethod($context->getAction());
        $controllerManager = $context->getControllerManager();
        $key = $controller->getName() . '/' . $context->getAction();
        $signal = new \Event_Signal(
            array(
                'controller'    => $controller,
                'context'       => $context
            ),
            $key
        );
        if (!empty($actionScheme['After'])) {
            $actions = reset($actionScheme['After']);
            foreach ($actions as $action) {
                if (strpos($action, '/') !== false) {
                    list($controllerName, $actionName) = explode('/', $action);
                } else {
                    $controllerName = $controller->getName();
                    $actionName = $action;
                }
                $slot = new \Event_Slot_Controller_After();
                $slot->setParams(array(
                    'action'    => array($controllerName, $actionName)
                ));
                $controllerManager->getService('eventManager')->register(
                    $signal, $slot
                ); 
            }
        }
    }
}