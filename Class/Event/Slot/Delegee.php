<?php

/**
 * Слот, делигирующий на другие слоты/сигналы
 * 
 * @author morph
 */
class Event_Slot_Delegee extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $params = $this->getParams();
        $buffer = $params['task']->getTransaction()->buffer();
        if (!empty($buffer['origin']) || !$buffer) {
            return;
        }
        $controllerManager = $params['context']->getControllerManager();
        $serviceLocator = $controllerManager->getServiceLocator();
        $eventManager = $serviceLocator->getService('eventManager');
        if (isset($params['slots'])) {
            foreach ($params['slots'] as $slot) {
                $slot = $eventManager->getSlot(reset($slot));
                $slot->setParams($buffer);
                $slot->action();
            }
        } elseif (isset($params['signal'])) {
            foreach ($params['signal'] as $signal => $params) {
                $signal = $eventManager->getSignal($signal);
                $signal->setData($params);
                $signal->notify();
            }
        }
    }
}