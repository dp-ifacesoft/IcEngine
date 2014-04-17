<?php

/**
 * Проверка на то является ли входящий аргумент массивом
 * 
 * @author goorus, morph
 */
class Data_Validator_Is_Array extends Data_Validator_Abstract
{
	/**
     * @inheritdoc
     */
	public function validate($data, $value = true)
	{
        $value = is_null($value) ? true : $value;
		return (bool) is_array($data) === $value;
	}
}