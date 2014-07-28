<?php

/**
 * Ресурс для хранения в сессии
 *
 * @author goorus, morph
 * @Service("sessionResource", disableConstruct=true)
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
	 * Конструктор
     *
	 * @param string $name Название ресурса в сессии
     * @param boolean $autoCreate Будет ли автоматически создан ресурс сессии
	 */
	public function __construct($name, $autoCreate = true)
	{
		$this->name = $name;
        if (!$name) {
            return;
        }
        if (isset($_SESSION[$name])) {
            $this->data = &$_SESSION[$name];
        } elseif ($autoCreate) {
            $_SESSION[$name] = array();
            $this->data = &$_SESSION[$name];
        }
	}

    /**
     * Проверяет существует ли ресурс сессии
     */
    public function exists($key = null)
    {
        return isset($_SESSION[$this->name]);
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