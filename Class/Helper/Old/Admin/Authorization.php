<?php
/**
 * 
 * @desc Помощник для авторизации пользователя
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Old_Admin_Authorization
{
	
	/**
	 * @desc Авторизовать пользователя в админке
	 * @param User $user Пользователь
	 * @throws Exception
	 */
	public static function authorize (User $user)
	{
		throw new Exception ("Not realised.");
	}
	
	/**
	 * @desc Выход из админки
	 */
	public static function unauthorize ()
	{
		throw new Exception ("Not realised.");
	}
	
}