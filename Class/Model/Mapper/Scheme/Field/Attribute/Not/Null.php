<?php

/**
 * @Атрибут поля not null для схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Attribute_Not_Null extends Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::filter
	 */
	public function filter ($value)
	{
		return is_null ($value) ? '' : $value;
	}

	/**
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::validate
	 */
	public function validate ($value)
	{
		return !$value ? !is_null ($value) : true;
	}
}
