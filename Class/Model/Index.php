<?php

/**
 * Индекс модели
 * 
 * @author morph, goorus
 */
class Model_Index
{
	/**
	 * Поле индекса
	 * 
     * @var string|array
	 */
	protected $fields;

	/**
	 * Название индекса
	 * 
     * @var string
	 */
	protected $name;

	/**
	 * Тип индекса
	 * 
     * @var string
	 */
	protected $type;

	/**
     * Конструктор
     * 
	 * @param string $name Название индекса
	 * @param string $type Тип индекса
	 * @param string|array field Поле (поля) индекса
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Получить поля (поля) индекса
	 * 
     * @return string|array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Получить название индекса
	 * 
     * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Получить тип индекса
	 * 
     * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Задает поля индекса
	 * 
     * @param array $fields
	 * @return Model_Index
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Задает тип индекса
	 * 
     * @param string $type
	 * @return Model_Index
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
}