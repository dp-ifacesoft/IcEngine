<?php

/**
 * @desc Абстрактное поле схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * @desc Атрибуты поля
	 * @var Model_Mapper_Scheme_Field_Attribute_Set
	 */
	protected $_attributes;

	/**
	 * (non-PHPDoc)
	 */
	public function __construct ()
	{
		$this->_attributes = new Model_Mapper_Scheme_Field_Attribute_Set;
	}

	/**
	 * @desc Получить атрибуты поля
	 * @return Model_Mapper_Scheme_Field_Attribute_Set
	 */
	public function attributes ()
	{
		return $this->_attributes;
	}

	/**
	 * @desc Получить имя фабрики
	 * @return string
	 */
	public function factoryName ()
	{
		return 'Field';
	}

	/**
	 * @desc Получить имя поля
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 26);
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