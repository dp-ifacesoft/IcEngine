<?php

/**
 * Является ли переменная объектом
 * 
 * @author morph
 */
class Data_Validator_Is_Object extends Data_Validator_Abstract 
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value = true)
	{
        $value = is_null($value) ? true : $value;
        return (bool) (is_object($data) === $value);
    }
}