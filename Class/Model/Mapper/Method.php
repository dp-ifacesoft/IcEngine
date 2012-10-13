<?php

/**
 * @desc Фабрика методов ORM
 * @author Илья Колесников
 */
class Model_Mapper_Method
{
	/**
	 * @desc Получить метод по имени
	 * @param string $name
	 * @return Model_Mapper_Method_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Method_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Method_Exception ('Method had not found');
		}
		return new $class_name;
	}

	/**
	 * @desc Привести имя метод из вида methodName к виду Method_Name
	 * @param string $name
	 */
	public static function normalizaName ($name)
	{
		$matches = array ();
		$reg_exp = '#([A-Z]*[a-z]+)#';
		preg_match_all ($reg_exp, $name, $matches);
		if (empty ($matches [1][0]))
		{
			return $name;
		}
		return implode ('_', array_map ('ucfirst', $matches [1]));
	}
}