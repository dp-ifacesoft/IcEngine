<?php

/**
 * 
 * @desc	Проверка имени.
 * @author	Юрий
 *
 */

class Data_Validator_Registration_Name
{

	const SHORT	= 'short';
	
	const LONG	= 'long';
	
	public function validateEx ($field, $data, $scheme)
	{
		$length = strlen ($data->$field);
		$param = $scheme->$field;
		
		if (isset ($param ['minLength']) && $length < $param ['minLength'])
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		if (isset ($param ['maxLength']) && $length > $param ['maxLength'])
		{
			return __CLASS__ . '/' . self::LONG;
		}
		
		return true;
	}

}