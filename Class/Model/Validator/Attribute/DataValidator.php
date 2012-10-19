<?php

class Model_Validator_Attribute_DataValidator extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		$result = true;

		foreach ($value as $validator)
		{
			$validator = Data_Validator_Manager::get ($validator);
			$current = false;
			if ($validator)
			{
				$current = $validator->validate ($model->sfield ($field));
			}
			$result &= $current;
		}

		return $result;
	}
}