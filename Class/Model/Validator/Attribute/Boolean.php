<?php

class Model_Validator_Attribute_Boolean extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return is_bool ($model->sfield ($field)) === (bool) $value;
	}
}