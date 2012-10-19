<?php

class Model_Validator_Attribute_Required extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return $model->sfield ($field) != null;
	}
}