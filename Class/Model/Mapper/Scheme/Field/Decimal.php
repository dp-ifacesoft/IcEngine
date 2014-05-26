<?php

/**
 * @desc Тип поля decimal схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Decimal extends Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate ($value)
	{
		return is_float ($value);
	}
}