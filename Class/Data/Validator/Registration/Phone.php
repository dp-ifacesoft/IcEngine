<?php

/**
 * 
 * @desc Проверка корректности телефонного номера.
 * @author Юрий
 *
 */

class Data_Validator_Registration_Phone
{
	
	const SHORT	= 'short';
	
	const LONG	= 'long';
	
	public function validateEx ($field, $data, $scheme)
	{
		$length = strlen ($data->$field);
		$param = $scheme->$field;
		
		if ($length < $param ['minLength'])
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		if ($length > $param ['maxLength'])
		{
			return __CLASS__ . '/' . self::LONG;
		}
    	
		return true;
	}
	
}