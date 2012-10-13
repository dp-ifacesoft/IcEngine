<?php

/**
 * @desc Абстрактная модель индекса схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Index_Abstract
{
	/**
	 * @desc Поля индекса
	 * @var array
	 */
	protected $_fields;

	/**
	 * @desc Получить имя фабрики
	 * @return string
	 */
	public function factoryName ()
	{
		return 'Index';
	}

	/**
	 * @desc Получить поля индекса
	 * @return array
	 */
	public function getFields ()
	{
		return $this->_fields;
	}

	/**
	 * @desc Получить имя индекса
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 26);
	}

	/**
	 * @desc Изменить поля индекса
	 * @param array $fields
	 */
	public function setFields ($fields)
	{
		$this->_fields = $fields;
	}
}