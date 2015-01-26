<?php

/**
 * Фильтр выхода после завершения контроллера
 *
 * @author morph
 */
class Event_Slot_Controller_OutputFilter extends Event_Slot
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
        $filterName = !empty($params['filterName']) 
            ? $params['filterName'] : null;
        if ($filterName) {
            $serviceLocator = IcEngine::serviceLocator();
            $filterManager = $serviceLocator->getService('dataFilterManager');
            $filter = $filterManager->get($filterName);
            if (!$filter) {
                return;
            }
            $transaction = $params['controller']->getTask()->getTransaction();
            $buffer = $filter->filter($transaction->buffer());
            $transaction->flush();
            $transaction->send('data', $buffer);
        }
    }
}