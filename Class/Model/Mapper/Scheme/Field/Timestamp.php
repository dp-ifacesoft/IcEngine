<?php

/**
 * @desc Тип поля timestamp схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Timestamp extends Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate ($value)
	{
		return is_numeric ($value);
	}
}