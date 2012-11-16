<?php
/**
 *
 * @desc Проверка валидности пароля.
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Validator_Registration_Password
{

	/**
	 * @desc Шаблон пароля по умолчанию
	 * @var string
	 */
	const DEFAULT_PATTERN = '/^[a-zA-Z0-9 ,\.\+\=\[\]\\/;:<>\'!@#\{\}]{6,20}$/';


	const SHORT	= 'short';	// Короткий пароль

	const LONG	= 'long';	// Короткий пароль

	const BAD = 'bad'; // не подходит по маске

	public function validate ($data)
	{
		return (bool) preg_match (self::DEFAULT_PATTERN, $data);
	}

	public function validateEx ($field, $data, $scheme)
	{
		$length = strlen ($data->$field);
		
		if (!$scheme->$field) {
			return __CLASS__ . '/' . self::BAD;
		}
		
		if (is_object($scheme)) {
			$param = $scheme->$field->__toArray ();
		} else {
			$param = $scheme[$field];
		}
		$min = isset ($param ['minLength']) ? $param ['minLength'] : 6;
		$max = isset ($param ['maxLength']) ? $param ['maxLength'] : 50;

		if ($length < $min)
		{
		    return __CLASS__ . '/' . self::SHORT;
		}

		if ($length > $max)
		{
			return __CLASS__ . '/' . self::LONG;
		}

		if (isset ($param ['pattern']))
		{
			if (!preg_match ($param ['pattern'], $data->$field))
			{
				return __CLASS__ . '/' . self::BAD;
			}
		}

		return true;
	}

}