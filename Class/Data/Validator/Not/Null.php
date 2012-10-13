<?php
/**
 * 
 * @desc Валидатор отличия от null.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Validator_Not_Null extends Data_Validator_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Validator_Abstract::validate()
	 */
	public function validate ($data)
	{
		return $data != null;
	}
	
}