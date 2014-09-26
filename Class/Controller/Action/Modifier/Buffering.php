<?php

/**
 * Буферизация вывода выполняемого контроллера
 * 
 * @author morph
 */
class Controller_Action_Modifier_Buffering extends 
    Controller_Action_Modifier_Abstract
{
    /**
     * @inheritdoc
     */
    public function run($state) 
    {
        $slot = new Event_Slot_Modifier_Buffering();
        $slot->setParams(array(
            'file'  => isset($this->args['file']) ? $this->args['file'] : ''
        ));
        $controllerManager = IcEngine::serviceLocator()->getService(
            'controllerManager'
        );
        $eventManager = $controllerManager->getService('eventManager');
        $key = implode('/', $state->getTask()->controllerAction());
        $signal = $eventManager->getSignal($key);
        $eventManager->register($signal, $slot);
        ob_start();
    }
}