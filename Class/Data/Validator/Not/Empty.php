<?php
/**
 * 
 * @desc Валидатор истинности выражения.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Validator_Not_Empty extends Data_Validator_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Validator_Abstract::validate()
	 */
	public function validate ($data)
	{
		return (bool) $data;
	}
	
}