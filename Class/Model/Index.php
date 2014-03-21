<?php

/**
 * @desc Индекс модели
 * @author morph, goorus
 */
class Model_Index
{
	/**
	 * @desc Поле индекса
	 * @var string|array
	 */
	protected $_fields;

	/**
	 * @desc Название индекса
	 * @var string
	 */
	protected $_name;

	/**
	 * @desc Тип индекса
	 * @var string
	 */
	protected $_type;

	/**
	 * @param string $name Название индекса
	 * @param string $type Тип индекса
	 * @param string|array field Поле (поля) индекса
	 */
	public function __construct ($name)
	{
		$this->_name = $name;
	}

	/**
	 * @desc Получить поля (поля) индекса
	 * @return string|array
	 */
	public function getFields ()
	{
		return $this->_fields;
	}

	/**
	 * @desc Получить название индекса
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}

	/**
	 * @desc Получить тип индекса
	 * @return string
	 */
	public function getType ()
	{
		return $this->_type;
	}

	/**
	 * @desc Задает поля индекса
	 * @param array $fields
	 * @return Model_Index
	 */
	public function setFields ($fields)
	{
		$this->_fields = $fields;
		return $this;
	}

	/**
	 * @desc Задает тип индекса
	 * @param string $type
	 * @return Model_Index
	 */
	public function setType ($type)
	{
		$this->_type = $type;
		return $this;
	}
}