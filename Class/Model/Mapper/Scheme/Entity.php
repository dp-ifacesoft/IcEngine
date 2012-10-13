<?php

/**
 * @desc Сущность схемы связей моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Entity
{
	/**
	 * @desc Имя класса
	 * @var string
	 */
	private $_class;

	/**
	 * @desc Имя сущности
	 * @var string
	 */
	private $_name;

	/**
	 * @desc Значение сущности
	 * @var mixed
	 */
	private $_value;

	/**
	 * @param string $class Имя класса
	 * @param string $name Название
	 * @param mixed $value  Значение
	 */
	public function __construct ($class, $name, $value)
	{
		$this->_class = $class;
		$this->_name = $name;
		$this->_value = $value;
	}

	/**
	 * @desc Получить класс сущности
	 * @return string
	 */
	public function getClass ()
	{
		return $this->_class;
	}

	/**
	 * @desc Получить имя сущности
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}

	/**
	 * @desc Получить значение сущности
	 * @return mixed
	 */
	public function getValue ()
	{
		return $this->_value;
	}

	/**
	 * @desc Изменить имя класса
	 * @param string $class
	 */
	public function setClass ($class)
	{
		$this->_class = $class;
	}

	/**
	 * @desc Изменить имя сущности
	 * @param string $name
	 */
	public function setName ($name)
	{
		$this->_name = $name;
	}

	/**
	 * @desc Изменить значение сущности
	 * @param mixed $value
	 */
	public function setValue (Model_Mapper_Scheme_Entity $value)
	{
		$this->_value = $value;
	}
}