<?php
/**
 * 
 * @desc Помощник для работы с email.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Email
{
	
	/**
	 * @desc Получает имя пользователя из адреса ящика.
	 * @param string $email Электронный адрес.
	 * @return string Часть, предшествующая @.
	 */
	public static function extractName ($email)
	{
		return substr ($email, 0, strpos ($email, '@'));
	}
	
}