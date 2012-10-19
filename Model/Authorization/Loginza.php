<?php

/**
 *
 * @desc Авторизация через логинзу.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Loginza extends Authorization_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize ($data)
	{
		$token = Authorization_Loginza_Token::tokenData ();

		$loginza = User_Loginza::byToken ($token, true, true);

		$user = $loginza ? $loginza->User : null;

		if (!$user)
		{
			$user = $this->autoregister ($token);
		}

		return $user instanceof User ? $user->authorize () : $user;
	}

	/**
	 * @desc Авторегистрация
	 * @param Authorization_Loginza_Token $token
	 * @return User|string
	 */
	public function autoregister (Authorization_Loginza_Token $token)
	{
		if (!$token->email)
		{
			return "Data_Validator_Loginza_Token::invalid";
		}

		$data = $token->data ('data');

		$user = User::create (array (
			'name'		=> $token->extractName (),
			'login'		=> (string) $token->identity,
			'email'		=> (string) $token->email,
			'password'	=> md5 (time ()),
			'phone'		=>
				isset ($data ['phone']) && is_string ($data ['phone']) ?
					$data ['phone'] :
					'',
			'active'	=> 1
		));

		$ul = Model_Manager::byKey (
			'User_Loginza',
			Query::instance ()
				->where ('identity', (string) $token->identity)
		);

		if ($ul)
		{
			$ul->update (array (
				'User__id'	=> $user->key ()
			));
		}
		else
		{
			$ul = new User_Loginza (array (
				'User__id'	=> $user->key (),
				'identity'	=> (string) $token->identity,
				'email'		=> (string) $token->email,
				'provider'	=> (string) $token->provider,
				'data'		=> json_encode ($data),
				'createdAt'	=> Helper_Date::toUnix ()
			));
			$ul->save ();
		}

		return $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered ($login)
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		$token = Authorization_Loginza_Token::tokenData ();
		$loginza = User_Loginza::byToken ($token);

		return $loginza ? $loginza->User : null;
	}

}