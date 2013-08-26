<?php

/**
 *
 * @desc Авторизация по логину и паролю.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Login_Password extends Authorization_Abstract
{

	/**
	 * @desc Configuration
	 * @var array
	 */
	protected static $_config = array (
		// Авторегистрация
		'autoregister'			=> false,

		// Валидатор логина
		'login_validator'		=> 'Email'
	);

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize ($data)
	{
		$config = $this->config ();

		$login = $data ['login'];
		$password = $data ['password'];
		$pass_md5 = md5 ($password);

		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
			->where ('login', $login)
			// На случай нескольких с одинаковыми мылами
			->order (array (
				'md5(`password`)="' . $pass_md5 . '"' => Query::DESC
			))
		);

		if ($user)
		{
			if ($user->password != $password)
			{
				return 'Data_Validator_Authorization_Password/invalid';
			}

			return $user->authorize ();
		}

		$validator = $config ['login_validator'];
		$login_valid = Data_Validator_Manager::validate (
			$validator,
			$login
		);

		if (!$login_valid)
		{
			// Не подходящий логин
			return 'Data_Validator_' . $validator . '/invalid';
		}

		$user = $this->autoregister ($data);

		return $user instanceof User ? $user->authorize () : $user;
	}

	/**
	 * @desc авторегистрация
	 */
	public function autoregister ($data)
	{
		$login = $data ['login'];
		$password = $data ['password'];

		$validator = $this->config ()->login_validator;
		$login_valid = Data_Validator_Manager::validate (
			$validator,
			$login
		);

		if (!$login_valid)
		{
			return 'Data_Validator_' . $validator . '/invalid';
		}

		$valid = Data_Validator_Manager::validate (
			'Registration_Password',
			$password
		);

		if (!$valid)
		{
			return 'Data_Validator_Registration_Password/bad';
		}

		$user = User::create (array (
			'login'		=> $login,
			'email'		=> $login,
			'name'		=> Helper_Email::extractName ($login),
			'password'	=> $password,
			'phone'		=> '',
			'active'	=> 1
		));

		return $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered ($login)
	{
		if (empty ($login))
		{
			return false;
		}

		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('login', $login)
		);

		return (bool) $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		return Data_Validator_Manager::validate (
			$this->config ()->login_validator,
			$login
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		return Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('login', $data ['login'])
		);
	}

}