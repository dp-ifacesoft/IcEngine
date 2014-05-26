<?php

class Model_Validator_Attribute_Numeric extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return is_numeric ($model->sfield ($field)) === (bool) $value;
	}
}