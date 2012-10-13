<?php

/**
 * 
 * Стандартная проверка числа.
 * @author Юрий
 *
 */

class Data_Validator_Standart_Integer
{
	
	/**
	 * Слишком маленькое число
	 * @var string
	 */
	const SMALL	= 'small';
	
	/**
	 * Слишком большое число
	 * @var string
	 */
	const BIG	= 'big';
	
    public function validateEx ($field, $data, $scheme)
    {
    	$val = $data->$field;
    	$param = $scheme->$field;
    	
		if (isset ($param ['min']) && $val < $param ['min'])
		{
			return __CLASS__ . '/' . self::SMALL;
		}
		
		if (isset ($param ['max']) && $val > $param ['max'])
		{
			return __CLASS__ . '/' . self::BIG;
		}
    	
		return true;
    }
	
}