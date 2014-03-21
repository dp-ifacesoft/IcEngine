<?php

class Model_Validator_Attribute_Object extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return is_object ($model->sfield ($field)) === (bool) $value;
	}
}