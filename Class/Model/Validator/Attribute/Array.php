<?php

class Model_Validator_Attribute_Array extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return is_array ($model->sfield ($field)) === (bool) $value;
	}
}