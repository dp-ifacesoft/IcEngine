<?php

class Model_Validator_Attribute_String extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return is_string ($model->sfield ($field)) === (bool) $value;
	}
}