<?php

/**
 * Валидатор истинности выражения
 * 
 * @author goorus, morph
 */
class Data_Validator_Not_Empty extends Data_Validator_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function validate($data, $value = true)
	{
		return !empty($data);
	}   
}