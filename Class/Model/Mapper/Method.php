<?php

/**
 * Фабрика методов ORM
 * 
 * @author Илья Колесников
 * 
 * @package Ice\Orm
 * @Service("modelMapperMethod")
 */
class Model_Mapper_Method
{
	/**
	 * Получить метод по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Method_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Method_' . $name;
		return new $className;
	}

	/**
	 * Привести имя метод из вида methodName к виду Method_Name
	 * 
     * @param string $name
     * @return string
	 */
	public function normalizaName($name)
	{
		$matches = array ();
		$reg_exp = '#([A-Z]*[a-z]+)#';
		preg_match_all($reg_exp, $name, $matches);
		if (empty ($matches[1][0])) {
			return $name;
		}
		return implode('_', array_map('ucfirst', $matches[1]));
	}
}