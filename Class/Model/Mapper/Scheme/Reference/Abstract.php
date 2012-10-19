<?php

/**
 * @desc Абстрактная модель ссылки схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @desc Поле ссылки
	 * @var string
	 */
	protected $_field;

	/**
	 * @desc Модель для ссылки
	 * @var string
	 */
	protected $_model;

	/**
	 * @desc Получить данные ссылки
	 * @param string $model_name
	 * @param string $id
	 * @return Model_Collection
	 */
	public function data ($model_name, $id)
	{

	}

	/**
	 * @desc Получить имя фабрики
	 * @return string
	 */
	public function factoryName ()
	{
		return 'Reference';
	}

	/**
	 * @desc Получить имя поля ссылки
	 * @return array
	 */
	public function getField ()
	{
		return $this->_field;
	}

	/**
	 * @desc Получить имя модели для ссылки
	 * @return string
	 */
	public function getModel ()
	{
		return $this->_model;
	}

	/**
	 * @desc Получить имя индекса
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 30);
	}

	/**
	 * @desc Возвращает ресурс схемы связей модели
	 * @return Model_Mapper_Scheme_Resource
	 */
	public function resource ()
	{
		return new Model_Mapper_Scheme_Resource ($this);
	}

	/**
	 * @desc Изменить поле ссылки
	 * @param string $field
	 */
	public function setField ($field)
	{
		$this->_field = $field;
	}

	/**
	 * @desc Изменить модель ссылки
	 * @param string $model
	 */
	public function setModel ($model)
	{
		$this->_model = $model;
	}
}