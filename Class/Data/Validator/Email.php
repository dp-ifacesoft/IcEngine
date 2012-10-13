<?php
/**
 * 
 * @desc Валидатор e-mail адресов
 * @author Юрий
 * @package IceEngine
 *
 */
class Data_Validator_Email extends Data_Validator_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Validator_Abstract::validate()
	 */
	public function validate ($data)
	{
		return
			$data && 
			$data == filter_var ($data, FILTER_VALIDATE_EMAIL);
	}
	
}