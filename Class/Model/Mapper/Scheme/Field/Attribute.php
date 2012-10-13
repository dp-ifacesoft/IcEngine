<?php

/**
 * @desc Фабрика атрибутов полей схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Attribute
{
	/**
	 * @desc Получить атрибут поля по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Field_Attribute_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Field_Attribute_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Scheme_Field_Attribute_Exception ('Field had not found');
		}
		return new $class_name;
	}
}