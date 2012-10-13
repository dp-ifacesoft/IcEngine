<?php

/**
 * @desc Фабрика полей схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field
{
	/**
	 * @desc Получить поле по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Field_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Field_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Scheme_Field_Exception ('Field had not found');
		}
		return new $class_name;
	}
}