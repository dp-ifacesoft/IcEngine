<?php

Loader::load ('Model_Validator_Attribute_Abstract');

class Model_Validator_Attribute_SameAs extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return (isset ($input [$value]) &&
			$model->sfield ($field) === $input [$value]);
	}
}