<?php

/**
 * @desc Абстрактный атрибут поля схемы связей модели
 * @author Илья Колесников
 */

class Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
	 * @desc Значение атрибута поля
	 * @var string
	 */
	protected $_value;

	/**
	 * @desc Получить имя фабрики
	 * @return string
	 */
	public function factoryName ()
	{
		return 'Field_Attribute';
	}

	/**
	 * @desc Получить имя атрибута
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 36);
	}

	/**
	 * @desc Получить значение атрибута поля
	 * @return string
	 */
	public function getValue ()
	{
		return $this->_value;
	}

	/**
	 * @desc Корректировать значение поля
	 * @param mixed $value
	 * @return mixed
	 */
	public function filter ($value)
	{
		return $value;
	}

	/**
	 * @desc Установить значение атрибута поля
	 * @param string $value
	 */
	public function setValue ($value)
	{
		$this->_value = $value;
	}

	/**
	 * @desc Проверить значение поля на корректность
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate ($value)
	{
		return true;
	}
}