<?php
/**
 *
 * @desc Авторизация по емейлу и паролю
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Email_Password extends Authorization_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize ($data)
	{
		$email = $data ['login'];
		$password = $data ['password'];
		$pass_md5 = md5 ($password);

		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
			->where ('email', $email)
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

		if (!Data_Validator_Manager::validate ('Email', $email))
		{
			// это даже не мыло
			return 'Data_Validator_Email/bad';
		}

		$user = $this->autoregister ($data);

		return $user instanceof User ? $user->authorize () : $user;
	}

	/**
	 * @desc авторегистрация
	 */
	public function autoregister ($data)
	{
		$email = $data ['login'];
		$password = $data ['password'];

		$valid = Data_Validator_Manager::validate (
			'Email',
			$email
		);

		if (!$valid)
		{
			return 'Data_Validator_Email/bad';
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
			'email'		=> $email,
			'name'		=> Helper_Email::extractName ($email),
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
				->where ('email', $login)
		);

		return (bool) $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		return
			$login &&
			$login == filter_var ($login, FILTER_VALIDATE_EMAIL);
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
				->where ('email', $data ['login'])
		);
	}

}