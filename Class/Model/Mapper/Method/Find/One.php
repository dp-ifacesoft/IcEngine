<?php

/**
 * @desc Метод схемы связей модели, возвращающий первую модель,
 * попадающую под условие
 * @author Илья Колесников
 */
class Model_Mapper_Method_Find_One extends Model_Mapper_Method_Abstract
{
	/**
	 * @desc Критерия поиска модели
	 * @var array
	 */
	private $_criteria = array ();

	/**
	 * @desc Добавить критерию поиска модели
	 * @param mixed
	 * @return Model_Mapper_Method_Find_One
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
	 * @desc Получить модель
	 * @return Model
	 */
	public function get ()
	{
		$query = Query::factory ('Select');
		foreach ($this->_criteria as $arg => $value)
		{
			$query->where ($arg, $value);
		}
		return Model_Manager::byQuery (
			$this->_params [0], $query
		);
	}
}