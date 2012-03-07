<?php

Loader::load ('Model_Mapper_Scheme_Field_Abstract');

/**
 * @desc Тип поля date схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Date extends Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate ($value)
	{
		return is_string ($value);
	}
}