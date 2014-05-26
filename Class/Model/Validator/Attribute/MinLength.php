<?php

class Model_Validator_Attribute_MinLength extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		$field = $model->sfield ($field);
		return is_string ($field) && strlen ($field) >= $value;
	}
}