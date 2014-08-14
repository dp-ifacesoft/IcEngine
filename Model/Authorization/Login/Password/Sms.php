<?php

/**
 *
 * @desc Аавторизация через отправку пользователю СМС сообщения с кодом.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Login_Password_Sms extends Authorization_Abstract
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		// Авторизовать только пользователей, имеющих одну из ролей.
		// Роли перечисляются через запятую.
		'auth_roles_names'			=> 'admin',

		// Функция, вызываемая после успешной авторизации
		'authorization_callback'	=> null,

		// Минимальная длина кода
		'code_min_length'		=> 4,

		// Максимальная длина кода
		'code_max_length'		=> 6,

		// Валидатор логина
		'login_validator'		=> 'Email',

		// Время действительности СМС в секундах
		'sms_expiration'		=> 3600,
		// Тип активации
		'activation_type'		=> 'sms_auth',
		// Провайдер СМСок
		'sms_provider'			=> 'First_Success',
		// Параметры для провайдера
		'sms_provider_params'	=> array (
			'providers'			=> 'Sms_Smsru,Sms_Littlesms'
		),
		// Шабон СМСок
		'sms_mail_template'		=> 'sms_activate',
		// Тестовый режим
		'sms_test_mode'			=> true,

		/**
		 * @desc можно перечислить логины, пароли и телефоны пользователей.
		 * Если этот параметр array, то пользователи, не указанные в этом
		 * массиве не могут быть авторизованы этим методом.
		 * Логины должны быть указаные в нижнем регистре.
		 * @tutorial
		 * 	'users'	=> array (
		 * 		'admin'	=> array (
		 * 			'active'	=> true,
		 * 			'password'	=> 'password',
		 * 			'phone'		=> '+7 123 456 78 90'
		 * 		)
		 * 	)
		 */
		'users'	=> false
	);

	/**
	 * @desc Авторизация
	 */
	protected function _authorize (User $user)
	{
		$user->authorize ();

		$config = $this->config ();

		if (!$config ['authorization_callback'])
		{
			return ;
		}

		list ($class, $method) = explode (
			'::',
			$config ['authorization_callback']
		);

		call_user_func (
			array ($class, $method),
			$user
		);
	}

	/**
	 * @desc Дополнительная проверка пользователя перед началом авторизации
	 * до отправки кода СМС.
	 * @param User $user Пользователь.
	 * @param string $login Указанный логин.
	 * @param string $password Указанный пароль.
	 * @return boolean true, если нужно проверять дальше, иначе - false.
	 */
	protected function _prechekUser (User $user, $login, $password)
	{
		if (!$this->_userHasRole ($user))
		{
			return false;
		}

		$cfg_users = $this->config ()->users;

		if ($cfg_users === false)
		{
			// нет проверки
			return true;
		}

		// Приводим к нижнему регистру
		$login = strtolower ($login);
		$cfg = $cfg_users [$login];

		return
			$cfg &&
			Crypt_Manager::isMatch ($password, $cfg ['password']) &&
			$cfg ['phone'] == $user->phone &&
			$cfg ['active'];
	}

	/**
	 * @desc Дополнительная проверка пользователя перед авторизацией после
	 * проверки кода СМС.
	 * @param User $user Подходящий пользователь.
	 * @param string $login Логин, указанный при авторизации.
	 * @param string $password Пароль.
	 * @return boolean true, если успешно, иначе - false.
	 */
	protected function _postcheckUser (User $user, $login, $password)
	{
		return $this->_prechekUser ($user, $login, $password);
	}

	/**
	 * @desc Проверка на принадлежность пользователя к необходимой роли
	 * @param User $user Пользователь
	 * @return boolean
	 */
	protected function _userHasRole (User $user)
	{
		$roles = explode (',', $this->config ()->auth_roles_names);

		if (!$roles)
		{
			// Ролей не задано, авторизуем всех
			return true;
		}

		foreach ($roles as $role)
		{
			$role = Acl_Role::byName ($role);
			if ($user->hasRole ($role))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 * @param string $data ['login']
	 * @param string $data ['password']
	 * @param integer $data ['activation_id']
	 * @param string $data ['activation_code']
	 */
	public function authorize ($data)
	{
		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('login', $data ['login'])
				->where (
					'(
						md5(`password`)=md5(?) OR
						`password`=md5(?)
					)',
					array ($data ['password'], $data ['password'])
				)
				->where ('active', 1)
		);

		if (!$user)
		{
			return 'Data_Validator_Authorization_Password/invalid';
		}

		if (!$this->_postcheckUser($user, $data ['login'], $data ['password']))
		{
			return 'Data_Validator_Authorization_User/denied';
		}

		$activation = Model_Manager::byQuery (
			'Activation',
			Query::instance ()
				->where ('type', $this->config ()->activation_type)
				->where ('code', $data ['activation_code'])
				->where ('id', $data ['activation_id'])
				->where ('User__id', $user->id)
				->where ('expirationTime>?', Helper_Date::toUnix ())
				->where ('finished<1')
		);

		if (!$activation)
		{
			return 'Data_Validator_Activation_Code/invalid';
		}

		$activation->update (array (
			'finished'		=> $activation->finished + 1,
			'finishTime'	=> Helper_Date::toUnix ()
		));

		$this->_authorize ($user);

		return $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered ($login)
	{
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

	/**
	 * @desc Отправляет пользователю СМС для авторизации
	 * @param array $data
	 * @param string $data ['login']
	 * @param string $data ['password']
	 * @param User $data ['user']
	 * @return Activation
	 */
	public function sendActivationSms (array $data)
	{
		$user = $data ['user'];
		$provider = $data ['provider'];

		if (strcasecmp ($user->login, $data ['login']) != 0)
		{
			return 'Data_Validator_Authorization_User/unexists';
		}

		if (
			$user->password != $data ['password'] &&
			$user->password != md5 ($data ['password'])
		)
		{
			return 'Data_Validator_Authorization_Password/invalid';
		}

		if (!$user->active)
		{
			return 'Data_Validator_Authorization_User/unactive';
		}

		if (!$this->_prechekUser ($user, $data ['login'], $data ['password']))
		{
			return 'Data_Validator_Authorization_User/denied';
		}

		$config = $this->config ();

		$activation_code = Helper_Activation::generateNumeric (
			$config ['code_min_length'],
			$config ['code_max_length']
		);

		// Пробуем использовать старый код
		$activation = Model_Manager::byQuery (
			'Activation',
			Query::instance ()
				->where ('User__id', $user->id)
				->where ('address', $user->phone)
				->where ('finished<0')
				->where ('type', $config ['activation_type'])
				->where ('expirationTime>?', Helper_Date::toUnix ())
		);

		if ($activation)
		{
			// За каждое повторное использование, приближаем к финишу,
			// чтобы если первая СМС не дошла, можно было добиться повторной
			// отправки.
			$activation->update (array (
				'finished'	=> $activation->finished + 1
			));

			if (!isset ($data ['send']) || !$data ['send'])
			{
				return $activation;
			}

			$activation_code = $activation->code;
		}
		else
		{
			$exp_time = time () + $config ['sms_expiration'];
			$activation = Activation::create (array (
				'finished'			=> -2,
				'address'			=> $user->phone,
				'type'				=> $config ['activation_type'],
				'code'				=> $activation_code,
				'expirationTime'	=> Helper_Date::toUnix ($exp_time),
				'User__id'			=> $user->id
			));
		}

		/**
		 * @desc Провайдер
		 * @var Mail_Provider_Abstract
		 */
		$provider = Model_Manager::byQuery (
			'Mail_Provider',
			Query::instance ()
				->where ('name', $provider ? $provider : $config ['sms_provider'])
		);

		$message = Mail_Message::create (
			$config ['sms_mail_template'],
			$user->phone,
			$user->name,
			array (
				'code'			=> $activation_code,
				'session_id'	=> $activation->id
			),
			$user->id,
			$provider->id,
			$config ['sms_provider_params']->__toArray ()
		)->save ();

		if ($config ['sms_test_mode'])
		{
			echo 'sms test mode';
		}
		else
		{
			$message->send ();
		}

		return $activation;
	}

}