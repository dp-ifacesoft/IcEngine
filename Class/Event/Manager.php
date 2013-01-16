<?php

/**
 * Менеджер событий
 *
 * @author morph
 * @Service("eventManager")
 */
class Event_Manager
{
	/**
	 * Карта событий
	 *
	 * @var Event_Map
	 */
	protected static $map;

	/**
	 * Сигналы
	 *
	 * @var array
	 */
	protected static $signals;

	/**
	 * Слоты
	 *
	 * @var array
	 */
	protected static $slots;

	/**
	 * Добавляет слот сигналу
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slot $slot
	 */
	public function bind($signal, $slot)
	{
        $this->getMap()->add($signal, $slot);
	}

	/**
	 * Получить карту событий
	 *
	 * @return Event_Map
	 */
	public function getMap()
	{
		if (!self::$map) {
			self::$map = new Event_Map;
		}
		return self::$map;
	}

	/**
	 * Получить сигнал по имени
	 *
	 * @param string $signalName
	 * @return Event_Signal
	 */
	public function getSignal($signalName)
	{
        if (is_object($signalName)) {
            $signal = $signalName;
            $signalName = $signal->getName();
            self::$signals[$signalName] = $signal;
        } elseif (!isset(self::$signals[$signalName])) {
			$className = 'Event_Signal_' . $signalName;
            if (IcEngine::getLoader()->tryLoad($className)) {
                $signal = new $className;
            } else {
                $signal = new Event_Signal(array(), $signalName);
            }
            self::$signals[$signalName] = $signal;
		}
		return self::$signals[$signalName];
	}

	/**
	 * Получить слот по имени
	 *
	 * @param string $slotName
	 * @return Event_Slot
	 */
	public function getSlot($slotName)
	{
		if (!isset(self::$slots[$slotName])) {
			$className = 'Event_Slot_' . $slotName;
			self::$slots[$slotName] = new $className;
		}
		return self::$slots[$slotName];
	}

	/**
	 * Выполнить сигнал
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slog $data
	 */
	public function notify($signal, $data = array())
	{
        $signal = $this->getSignal($signal);
        $data = array_merge($signal->getData(), $data);
		$slots = $this->getMap()->getBySignal($signal);
		if ($slots) {
			foreach ($slots as $slot) {
				$slot->setParams(array_merge($data, $slot->getParams()));
				$slot->action();
			}
		}
	}

	/**
	 * Регистрирует слот на сигнал
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slog $slot
	 */
	public function register($signal, $slot)
	{
        self::$signals[$signal->getName()] = $signal;
        self::$slots[$slot->getName()] = $slot;
		$this->getMap()->add($signal, $slot);
	}

	/**
	 * Изменить карту событий
	 *
	 * @param Event_Map $map
	 */
	public function setMap($map)
	{
		self::$map = $map;
	}

	/**
	 * Снимает слот со сигнала
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slog $slot
	 */
	public function unbind($signal, $slot)
	{
		$this->getMap()->removeSlot($signal, $slot);
	}

	/**
	 * Снимает регистрацию слота с сигнала
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slog $slot
	 */
	public function unregister($signal, $slot)
	{
		$this->getMap()->removeSignal($signal, $slot);
	}
}