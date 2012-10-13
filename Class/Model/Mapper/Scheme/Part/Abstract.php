<?php

/**
 * @desc Абстрактная часть схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Part_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Part_Abstract::$_specification
	 */
	protected static $_specification;

	/**
	 * @desc Заполнить часть схемы
	 * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param Objective $values
	 * @return Model_Mapper_Scheme_Abstract
	 */
	public function execute ($scheme, $values)
	{

	}

	/**
	 * @desc Получить имя
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 25);
	}

	/**
	 * @desc Получить спецификацию
	 * @return string
	 */
	public function getSpecification ()
	{
		return static::$_specification;
	}
}