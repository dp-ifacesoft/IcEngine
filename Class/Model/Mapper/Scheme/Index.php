<?php

/**
 * @desc Фабрика индексов схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Index
{
	/**
	 * @desc Получить индекс по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Index_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Index_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Scheme_Index_Exception ('Index had not found');
		}
		return new $class_name;
	}
}