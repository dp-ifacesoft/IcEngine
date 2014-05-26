<?php

/**
 * @desc Тип поля varchar схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Varchar extends Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate ($value)
	{
		return is_string ($value);
	}
}