<?php

/**
 * Проверка на виладность урла
 * 
 * @author goorus, morph
 */
class Data_Validator_Url extends Data_Validator_Abstract
{
	/**
     * @inheritdoc
     */
	public function validate($data)
	{
		return (bool) filter_var($data, FILTER_VALIDATE_URL);
	}
}