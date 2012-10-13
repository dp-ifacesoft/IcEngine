<?php
/**
 * 
 * Валидатор дат
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Validator_Standart_Date
{
	
	public function validateEx ($field, $data, $scheme)
	{
		$val = $data->$field;
		$param = $scheme->$field;
		
		if (
			isset ($param ['min']) && 
			Helper_Date::cmpUnix ($val, $param ['min']) < 0
		)
		{
			return __CLASS__ . '/' . self::SMALL;
		}
		
		if (
			isset ($param ['max']) && 
			Helper_Date::cmpUnix ($val, $param ['max']) > 0
		)
		{
			return __CLASS__ . '/' . self::BIG;
		}
		
		return true;
	}
	
}