<?php

/**
 * @Атрибут поля null для схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Attribute_Null extends Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::filter
	 */
	public function filter ($value)
	{
		return !$value ? null : $value;
	}

	/**
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::validate
	 */
	public function validate ($value)
	{
		return !$value ? is_null ($value) : false;
	}
}
