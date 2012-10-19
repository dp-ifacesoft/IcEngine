<?php

class Model_Validator_Attribute_Additional extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		$result = true;

		foreach ($value as $validator)
		{
			list ($class_name, $method_name) = explode ('::', $validator);
			$result &= call_user_func_array (
				array ($class_name, $method_name),
				array ($model, $field, $value, $input)
			);
		}

		return $result;
	}
}