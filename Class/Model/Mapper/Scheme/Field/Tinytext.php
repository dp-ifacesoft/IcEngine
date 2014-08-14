<?php

/**
 * @desc Тип поля tinytext схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Tinytext extends Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate ($value)
	{
		return is_string ($value);
	}
}