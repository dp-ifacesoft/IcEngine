<?php

/**
 * 
 * Стандартная проверка строки
 * @author Юрий
 *
 */

class Data_Validator_Standart_String
{
	
	/**
	 * @desc Слишком короткая строка
	 * @var string
	 */
	const SHORT	= 'short';
	
	/**
	 * @desc Слишком длинная строка
	 * @var string
	 */
	const LONG	= 'long';
	
	/**
	 * @desc Не соответсвует маске
	 * @var string
	 */
	const REGEXP = 'regexp';
	
	/**
	 * @desc Валидация.
	 * @param strin $field
	 * @param stcClass $data
	 * @param stdClass $scheme
	 */
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
		
		if (isset ($param ['pattern']))
		{
			if (!preg_match ($param ['pattern'], $data->$field))
			{
				return __CLASS__ . '/' . self::REGEXP;
			}
		}
		
		return true;
	}
	
}