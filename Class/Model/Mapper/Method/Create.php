<?php

/**
 * Метод схемы связей модели, создающий модель
 *
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Method_Find extends Model_Mapper_Method_Abstract
{
	/**
	 * Поля модели
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Добавить поле модели
	 *
	 * @param mixed
	 * @return Model_Mapper_Method_Create
	 */
	public function with()
	{
		$args = func_get_args();
		if (count($args) == 2) {
			$args = array($args[0] => $args[1]);
		}
		foreach ($args as $arg => $value) {
			$this->fields[$arg] = $value;
		}
		return $this;
	}

	/**
	 * Создать модель
	 * 
	 * @return Model
	 */
	public function get()
	{
		return $this->getService('modelManager')->create(
            $this->params[0], $this->fields
		);
	}
}