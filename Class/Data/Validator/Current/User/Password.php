<?php

/**
 *
 * Проверка текущего пароля пользователя
 * @author Юрий
 *
 */
class Data_Validator_Current_User_Password extends Data_Validator_Abstract
{

	const INVALID = 'invalid';

	public function validate ($data)
	{
		if ($data != User::getCurrent ()->password)
		{
			return __CLASS__ . '/' . self::INVALID;
		}

		return true;
	}

}