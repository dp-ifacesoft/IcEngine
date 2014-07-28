<?php

/**
 * Карта событий
 *
 * @author morph
 */
class Event_Map
{
	/**
	 * Сигналы
	 *
	 * @var array
	 */
	protected $signals;

	/**
	 * Слоты
	 *
	 * @var array
	 */
	protected $slots;

	/**
	 * Добавить слот на сигнал
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slog $slot
	 */
	public function add($signal, $slot)
	{
		$this->signals[$slot->getName()][] = $signal;
		$this->slots[$signal->getName()][] = $slot;
 	}

	/**
	 * Получить слоты по сигналу
	 *
	 * @param Event_Signal $signal
	 * @return array
	 */
	public function getBySignal($signal)
	{
        $name = $signal->getName();
		return isset($this->slots[$name]) ? $this->slots[$name] : null;
	}

	/**
	 * Получить слоты по сигналу
	 *
	 * @param Event_Slot $slot
	 * @return array
	 */
	public function getBySlot($slot)
	{
        $name = $slot->getName();
		return isset($this->signals[$name]) ? $this->signals[$name] : null;
	}

	/**
	 * Получить сигналы
	 *
	 * @return array
	 */
	public function getSignals()
	{
		return $this->signals;
	}

	/**
	 * Получить слоты
	 *
	 * @return array
	 */
	public function getSlots()
	{
		return $this->slots;
	}

	/**
	 * Удалить слоты сигнала
	 *
	 * @param Event_Signal $signal
	 */
	public function removeBySignal($signal)
	{
		$this->slots[$signal->getName()] = array();
	}

	/**
	 * Удалить по слоту
	 *
	 * @param Event_Slog $slot
	 */
	public function removeBySlot($slot)
	{
		$this->signals[$slot->getName()] = array();
	}

	/**
	 * Удаляет все сигналы слота
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slot $slot
	 */
	public function removeSignal($signal, $slot)
	{
		$signals = $this->signals[$slot->getName()];
		$newSignals = array();
		foreach ($signals as $currentSignal) {
			if ($currentSignal->getName() != $signal->getName()) {
				$newSignals[] = $currentSignal;
			}
		}
		$this->signals[$slot->getName()] = $newSignals;
	}

	/**
	 * Удаляет все сигналы слота
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slot $slot
	 */
	public function removeSlot($signal, $slot)
	{
        if (!isset($this->slots[$signal->getName()])) {
            return;
        }
		$slots = $this->slots[$signal->getName()];
		$newSlots = array();
        if (!$slots) {
            return;
        }
		foreach ($slots as $currentSlot) {
			if ($currentSlot->getName() != $slot->getName()) {
				$newSlots[] = $currentSlot;
			}
		}
		$this->slots[$signal->getName()] = $newSlots;
	}

	/**
	 * Изменить сигналы
	 *
	 * @param array $signals
	 */
	public function setSignals($signals)
	{
		$this->signals = $signals;
	}

	/**
	 * Изменить слоты
	 *
	 * @param array $slots
	 */
	public function setSlots($slots)
	{
		$this->slots = $slots;
	}
}