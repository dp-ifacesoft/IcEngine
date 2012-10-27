<?php

/**
 * Сигналы события
 *
 * @author morph
 */
class Event_Signal
{
	/**
	 * Параметры сигнала
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Конструктор
	 *
	 * @param array $data
	 * @param string $name
	 */
	public function __construct($data = array(), $name = null)
	{
		$this->data = $data;
		$this->name = $name;
	}

	/**
	 * Добавить слот на сигнал
	 *
	 * @param Event_Slot $slot
	 */
	public function bind($slot)
	{
		if (is_string($slot)) {
			$slot = $this->getSlot($slot);
		}
		Event_Manager::bind($this, $slot);
	}

	/**
	 * Метод для получение данных сигнала
	 *
	 * @return array
	 */
	public function data()
	{

	}

	/**
	 * Получить данные сигнала
	 *
	 * @return array
	 */
	public function getData()
	{
		if (is_null($this->data)) {
			$this->data = (array) $this->data();
		}
		return $this->data;
	}

	/**
	 * Получить имя сигнала
	 *
	 * @return string
	 */
	public function getName()
	{
		if ($this->name) {
			return $this->name;
		}
		return substr(get_class($this), strlen(__CLASS__) + 1);
	}

	/**
	 * Получить слот по имени
	 *
	 * @param string $slogName
	 * @return Event_Slot
	 */
	protected function getSlot($slotName)
	{
		return Event_Manager::getSlot($slotName);
	}

	/**
	 * Выполнить сигнал
	 *
	 * @param array $data
	 */
	public function notify()
	{
		Event_Manager::notify($this, $this->getData());
	}

	/**
	 * Изменить аднные слота
	 *
	 * @param array $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * Снимает регистрацию со слота
	 *
	 * @param Event_Slot $slot
	 */
	public function unbind($slot)
	{
		if (is_string($slot)) {
			$slot = $this->getSlot($slot);
		}
		Event_Manager::unbind($this, $slot);
	}

	/**
	 * Удаляет сигнал
	 */
	public function unbindAll()
	{
		Event_Manager::removeSignal($this);
	}
}