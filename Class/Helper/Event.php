<?php

/**
 * Хелпер для событий
 * 
 * @author morph
 * @Service("helperEvent")
 */
class Helper_Event extends Helper_Abstract
{
    /**
     * Добавить слот на сигнал в начало очереди
     * 
     * @param mixed $signal
     * @param mixed $slot
     */
    public function prepend($signal, $slot)
    {
        $eventManager = $this->getService('eventManager');
        $signal = $eventManager->getSignal($signal);
        $slot = $eventManager->getSlot($slot);
        $map = $eventManager->getMap();
        $signals = $map->getSignals();
        $slots = $map->getSlots();
        $map->setSignals(array_merge(
            array($slot->getName() => $signal), $signals
        ));
        $map->setSlots(array_merge(
            array($signal->getName() => $slot), $slots
        ));
    }
}