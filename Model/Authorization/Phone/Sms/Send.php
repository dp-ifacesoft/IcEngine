<?php

/**
 *
 * @desc Аавторизация через отправку пользователю СМС сообщения с кодом.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Phone_Sms_Send extends Authorization_Abstract
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		// Авторегистрация
		'autoregister'			=> false,
		// Минимальная длина кода
		'code_min_length'		=> 4,
		// Максимальная длина кода
		'code_max_length'		=> 6,
		// Время действительности СМС
		'sms_expiration'		=> 3600,
		// префикс кода в БД
		'sms_prefix'			=> 'smsauth.',
		// Провайдер СМСок
		'sms_provider'			=> 'First_Success',
		// Параметры для провайдера
		'sms_provider_params'	=> array (
			'providers'			=> 'Sms_Smsru,Sms_Littlesms'
		),
		// Шабон СМСок
		'sms_mail_template'		=> 'sms_activate',
		// Тестовый режим
		'sms_test_mode'			=> true
	);

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize ($data)
	{
		$user = $this->findUser ($data);

		$prefix = $this->config ()->sms_prefix;

		$activation = Model_Manager::byQuery (
			'Activation',
			Query::instance ()
			->where ('code', $prefix . $data ['activation_code'])
			->where ('id', $data ['activation_id'])
			->where ('User__id', $user ? $user->id : 0)
			->where ('finished', false)
		);

		if (!$activation || $activation->finished)
		{
			return 'Data_Validator_Activation_Code/invalid';
		}

		$activation->update (array (
			'finished'	=> 1
		));

		if ($user)
		{
			return $user->authorize ();
		}

		// пользователь не зарегистрирован
		if (!$this->config ()->autoregister)
		{
			return 'Data_Validator_Authorization_User/unexists';
		}

		$user = $this->autoregister ($data, $activation);

		return $user instanceof User ? $user->authorize () : $user;
	}

	/**
	 * @desc Авторегистрация пользователя
	 * @param array $data Данные с формы авторизации.
	 * @param Activation $activation Пройденная активация.
	 * @return User|null
	 */
	public function autoregister ($data, Activation $activation)
	{
		$phone = $activation->address;

		$user = User::create (array (
			'name'		=> Helper_Phone::formatMobile ($phone),
			'email'		=> '',
			'password'	=> md5 (time ()),
			'phone'		=> $phone,
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
		$phone = Helper_Phone::parseMobile ($login);

		if (!$phone)
		{
			return false;
		}

		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
			->where ('phone', $phone)
		);

		return (bool) $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		$phone = Helper_Phone::parseMobile ($login);
		return (bool) $phone;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		$phone = Helper_Phone::parseMobile ($data ['login']);
		return Model_Manager::byQuery (
			'User',
			Query::instance ()
			->where ('phone', $phone)
		);
	}

	/**
	 * @desc Отправляет пользователю СМС для авторизации
	 * @param array $data
	 * @param string $data ['phone']
	 * @return Activation
	 */
	public function sendActivationSms (array $data)
	{
		$phone = Helper_Phone::parseMobile ($data ['phone']);

		if (!$phone)
		{
			return 'invalidPhone';
		}

		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('phone', $phone)
		);

		$config = $this->config ();

		$clear_code = Helper_Activation::generateNumeric (
			$config ['code_min_length'],
			$config ['code_max_length']
		);

		$activation_code = $config ['sms_prefix'] . $clear_code;

		$activation = Activation::create (array (
			'address'			=> $phone,
			'code'				=> $activation_code,
			'expirationTime'	=>
				Helper_Date::toUnix (time () + $config ['sms_expiration']),
			'User__id'			=> $user ? $user->id : 0
		));

		/**
		 * @desc Провайдер
		 * @var Mail_Provider_Abstract
		 */
		$provider = Model_Manager::byQuery (
			'Mail_Provider',
			Query::instance ()
			->where ('name', $config ['sms_provider'])
		);

		$message = Mail_Message::create (
			$config ['sms_mail_template'],
			$phone,
			$user ? $user->name : $phone,
			array (
				'code'			=> $clear_code,
				'session_id'	=> $activation->id
			),
			$user ? $user->id : 0,
			$provider->id,
			$config ['sms_provider_params']->__toArray ()
		)->save ();

		if (!$config ['sms_test_mode'])
		{
			$message->send ();
		}

		return $activation;
	}

}