<?php

/**
 * @Атрибут поля default для схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Attribute_Default extends Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::filter
	 */
	public function filter ($value)
	{
		return !$value ? $this->_value : $value;
	}
}
