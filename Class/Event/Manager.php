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
	protected $map;

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
		if (!$this->map) {
			$this->map = new Event_Map();
		}
		return $this->map;
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
            $this->signals[$signalName] = $signal;
        } elseif (!isset($this->signals[$signalName])) {
			$className = 'Event_Signal_' . $signalName;
            if (IcEngine::getLoader()->tryLoad($className)) {
                $signal = new $className;
            } else {
                $signal = new Event_Signal(array(), $signalName);
            }
            $this->signals[$signalName] = $signal;
		}
		return $this->signals[$signalName];
	}

	/**
	 * Получить слот по имени
	 *
	 * @param string $slotName
	 * @return Event_Slot
	 */
	public function getSlot($slotName)
	{
		if (!isset($this->slots[$slotName])) {
			$className = 'Event_Slot_' . $slotName;
			$this->slots[$slotName] = new $className;
		}
		return $this->slots[$slotName];
	}

    /**
     * Проверяет зарегистрирован ли сигнал в конфиге
     *
     * @param string $signalName
     * @return boolean
     */
    public function isSignalRegistered($signalName)
    {
        $locator = IcEngine::serviceLocator();
        $configManager = $locator->getService('configManager');
        $config = $configManager->get('Event_Manager');
        $signals = $config->__toArray();
        if (isset($signals[$signalName])) {
            return true;
        }
        return false;
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
                $slotParams = $slot->getParams();
				$slot->setParams(array_merge($data, $slotParams));
				$slot->action();
                $slot->setParams($slotParams);
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
        $this->signals[$signal->getName()] = $signal;
        $this->slots[$slot->getName()] = $slot;
		$this->getMap()->add($signal, $slot);
	}

	/**
	 * Изменить карту событий
	 *
	 * @param Event_Map $map
	 */
	public function setMap($map)
	{
		$this->map = $map;
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