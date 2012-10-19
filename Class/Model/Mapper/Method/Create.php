<?php

/**
 * @desc Метод схемы связей модели, создающий модель
 * @author Илья Колесников
 */
class Model_Mapper_Method_Find extends Model_Mapper_Method_Abstract
{
	/**
	 * @desc Поля модели
	 * @var array
	 */
	private $_fields = array ();

	/**
	 * @desc Добавить поле модели
	 * @param mixed
	 * @return Model_Mapper_Method_Create
	 */
	public function with ()
	{
		$args = func_get_args ();
		if (count ($args) == 2)
		{
			$args = array ($args [0] => $args [1]);
		}
		foreach ($args as $arg => $value)
		{
			$this->_fields [$arg] = $value;
		}
		return $this;
	}

	/**
	 * @desc Создать модель
	 * @return Model
	 */
	public function get ()
	{
		return Model_Manager::create (
			$this->_params [0],
			$this->_fields
		);
	}
}