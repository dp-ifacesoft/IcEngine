<?php

/**
 * Модификатор для запуска методов в классах(кроме контроллеров)
 *
 * @author neon
 */
class Controller_Action_Modifier_Run extends Controller_Action_Modifier_Abstract
{
    /**
     * @inheritdoc
     */
    public function run($state)
    {
        $this->before();
        $controllerManager = IcEngine::serviceLocator()->getService(
            'controllerManager'
        );
        $eventManager = $controllerManager->getService('eventManager');
        //print_r($state);die;
        $key = implode('/', $state->getTask()->controllerAction());
        $signal = $eventManager->getSignal($key);
        $eventManager->register($signal, $slot);
        ob_start();
        $this->after();
    }
}