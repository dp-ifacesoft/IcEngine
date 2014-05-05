<?php

class Model_Validator_Attribute_Regexp extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return preg_match ('#' . $value . '#', $model->sfield ($field));
	}
}