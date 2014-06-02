<?php

/**
 * @Атрибут поля unsigned для схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Attribute_Unsigned extends Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::filter
	 */
	public function filter ($value)
	{
		return abs ($value);
	}

	/**
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::validate
	 */
	public function validate ($value)
	{
		return $value >= 0;
	}
}
