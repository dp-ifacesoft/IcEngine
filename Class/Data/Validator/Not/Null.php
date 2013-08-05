<?php

/**
 * Валидатор отличия от null.
 * 
 * @author goorus, morph
 */
class Data_Validator_Not_Null extends Data_Validator_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function validate($data, $value = true)
	{
		return !is_null($data);
	}
}