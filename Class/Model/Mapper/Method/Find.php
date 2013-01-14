<?php

/**
 * @desc Метод схемы связей модели, возвращающий коллекцию моделей,
 * попадающую под условие
 * @author Илья Колесников
 */
class Model_Mapper_Method_Find extends Model_Mapper_Method_Abstract
{
	/**
	 * @desc Критерия поиска модели
	 * @var array
	 */
	private $_criteria = array ();

	/**
	 * @desc Добавить критерию поиска модели
	 * @param mixed
	 * @return Model_Mapper_Method_Find
	 */
	public function by ()
	{
		$args = func_get_args ();
		if (count ($args) == 2)
		{
			$args = array ($args [0] => $args [1]);
		}
		foreach ($args as $arg => $value)
		{
			$arg = trim ($arg);
			if (!ctype_alnum ($arg) && substr ($arg, -1, 1) !== '=')
			{
				$arg .= '?';
			}
			$this->_criteria [$arg] = $value;
		}
		return $this;
	}

	/**
	 * @desc Получить коллекцию
	 * @return Model_Collection
	 */
	public function get ()
	{
		$query = Query::factory ('Select');
		foreach ($this->_criteria as $arg => $value)
		{
			$query->where ($arg, $value);
		}
		return Model_Collection_Manager::byQuery (
			$this->_params [0], 	$query
		);
	}
}