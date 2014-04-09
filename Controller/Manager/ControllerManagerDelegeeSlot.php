<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для after-вызова
 * 
 * @author morph
 */
class ControllerManagerDelegeeSlot extends ControllerManagerDelegeeAbstract
{ 
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $scheme = $controller->getAnnotations();
        $actionScheme = $scheme->getMethod($context->getAction());
        if (empty($actionScheme['Slot'])) {
            return;
        }
        $controllerManager = $context->getControllerManager();
        $key = $controller->getName() . '/' . $context->getAction();
        $eventManager = $controllerManager->getService('eventManager');
        $signal = $eventManager->getSignal($key);
        $slot = $eventManager->getSlot('Delegee');
        $slot->setParams(array(
            'slots'     => $actionScheme['Slot'],
            'context'   => $context
        ));
        $eventManager->register($signal, $slot);
    }
}