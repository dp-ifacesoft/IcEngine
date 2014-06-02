<?php

class Model_Validator_Attribute_Resource extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return is_resource ($model->sfield ($field)) === (bool) $value;
	}
}