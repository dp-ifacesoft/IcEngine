<?php

/**
 * Являет ли значение переменная строкой
 * 
 * @author morph
 */
class Data_Validator_Is_String extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value = true)
	{
        $value = is_null($value) ? true : $value;
        return is_string($data) === $value;
	}
}