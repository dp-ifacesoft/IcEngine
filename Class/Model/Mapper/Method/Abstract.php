<?php

/**
 * @desc Абстрактный метод связей модели
 */
class Model_Mapper_Method_Abstract
{
	/**
	 * @desc Параметры методв
	 * @var array
	 */
	protected $_params;

	/**
	 * @desc Выполнить метод
	 */
	public function execute ()
	{
		return $this;
	}

	/**
	 * @desc Получить имя метода
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 20);
	}

	/**
	 * @desc Задать параметры метода
	 * @param array $params
	 */
	public function setParams ($params)
	{
		$this->_params = $params;
	}
}