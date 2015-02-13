<?php

/**
 * Проверка на максимальную длину
 * 
 * @author morph
 */
class Data_Validator_Max_Length extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value = null)
	{
		return is_string($data) && strlen($data) <= $value;
	}
}