<?php

/**
 * Менеджер событий
 *
 * @author morph
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
	public static function bind($signal, $slot)
	{
		self::getMap()->add($signal, $slot);
	}

	/**
	 * Получить карту событий
	 *
	 * @return Event_Map
	 */
	public static function getMap()
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
	public static function getSignal($signalName)
	{
		if (!isset(self::$signals[$signalName])) {
			$className = 'Event_Signal_' . $signalName;
			self::$signals[$signalName] = new $className;
		}
		return self::$signals[$signalName];
	}

	/**
	 * Получить слот по имени
	 *
	 * @param string $slotName
	 * @return Event_Slot
	 */
	public static function getSlot($slotName)
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
	public static function notify($signal, $data)
	{
		$slots = self::getMap()->getBySignal($signal);
		if ($slots) {
			foreach ($slots as $slot) {
				$slot->setParams($data);
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
	public static function register($signal, $slot)
	{
		self::getMap()->add($signal, $slot);
	}

	/**
	 * Изменить карту событий
	 *
	 * @param Event_Map $map
	 */
	public static function setMap($map)
	{
		self::$map = $map;
	}

	/**
	 * Снимает слот со сигнала
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slog $slot
	 */
	public static function unbind($signal, $slot)
	{
		self::getMap()->removeSlot($signal, $slot);
	}

	/**
	 * Снимает регистрацию слота с сигнала
	 *
	 * @param Event_Signal $signal
	 * @param Event_Slog $slot
	 */
	public static function unregister($signal, $slot)
	{
		self::getMap()->removeSignal($signal, $slot);
	}
}