<?php
/**
 *
 * @desc Ресурс для хранения в сессии.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Session_Resource extends Objective
{
	/**
	 * Имя объекта сессии
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @desc Создает и возвращает ресурс сесии.
	 * @param string $name Название ресурса в сессии.
	 */
	public function __construct ($name)
	{
		$this->name = $name;

		if (!isset ($_SESSION [$name]) || !is_array ($_SESSION [$name]))
		{
			$_SESSION [$name] = array ();
		}

		$this->_data = &$_SESSION [$name];
	}

	/**
	 * Получить имя ресурса
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Удалить ресурс сессии
	 */
	public function remove()
	{
		if (isset($_SESSION[$this->name])) {
			unset($_SESSION[$this->name]);
		}
	}

	/**
	 * Изменить имя ресурса
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
}