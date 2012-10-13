<?php

/**
 * @desc Фабрика ссылок схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference
{
	/**
	 * @desc Получить ссылку по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Reference_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Reference_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Scheme_Reference_Exception ('Reference had not found');
		}
		return new $class_name;
	}
}