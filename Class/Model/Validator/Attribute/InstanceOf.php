<?php

class Model_Validator_Attribute_InstanceOf extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return get_class ($model->sfield ($field)) == $value;
	}
}