<?php

/**
 * Действие по выполнению контроллера
 *
 * @author morph
 */
class Event_Slot_Controller_After extends Event_Slot
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
        $controllerManager->call(
            $params['action'][0], $params['action'][1], array(
                'before'    => $buffer,
                'args'      => $params['context']->getArgs()
            )
        );
    }
}