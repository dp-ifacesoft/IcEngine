<?php
/**
 * @desc Класс авторизации.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization
{

	public static $config = array (
		'login_field'		=> 'email',
		'password_field'	=> 'password'
	);

	/**
	 * @desc Попытка авторизации
	 * @param Data_Transport $input Вход контроллера
	 * @return User|string Пользователь или ошибка - строка вида
	 * "Тип_Авторизации::ошибка".
	 */
	public static function getAuthUser (Data_Transport $input)
	{
		$authes = Model_Collection_Manager::byQuery (
			'Authorization_Type',
			Query::instance ()
			->where ('active', 1)
			->order ('rank')
		);

		$error = 'noAuthMethod';

		foreach ($authes as $auth)
		{
			if ($auth->possibleAuth ($input))
			{
				$user = $auth->getAuthUser ($input);
				if ($user instanceof User)
				{
					return $user;
				}
				$error = $user;
			}
		}

		return $error;
	}

	/**
	 *
	 * @param string $login
	 * @param string $password
	 * @return User|null
	 */
	public static function findUser ($login, $password)
	{
		return Model_Manager::byQuery (
			'User',
			Query::instance ()
			->where (self::$config ['login_field'], $login)
			->where (self::$config ['password_field'], $password)
			->where ('active=1')
			->order (self::$config ['login_field'])
			->limit (1, 0)
		);
	}

	/**
	 *
	 * @param string $login
	 * @param string $password
	 * @return User|null
	 */
	public static function authorize ($login, $password)
	{
		$user = self::findUser ($login, $password);

		if ($user)
		{
			$user->authorize ();
		}

		return $user;
	}

	public static function logout ($redirect = '/')
	{
		User_Session::getCurrent ()->delete ();
		Header::redirect ($redirect);
	}

}