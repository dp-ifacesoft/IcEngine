<?php
/**
 *
 * @desc Аавторизация через отправку пользователю СМС сообщения с кодом.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Email_Password_Sms extends Authorization_Abstract
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		// Авторизовать только пользователей, имеющих одну из ролей.
		// Роли перечисляются через запятую.
		'auth_roles_names'			=> 'admin',

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
			'providers'			=> 'Sms_Littlesms,Sms_Dcnk,Sms_Yakoon'
		),
		// Шабон СМСок
		'sms_mail_template'		=> 'sms_activate',
		// Тестовый режим
		'sms_test_mode'			=> true,

		// Колбэки на авторизацию и выход
		'authorization_function'	=> 'Helper_Authorization_Admin::authorize',
		'unauthorization_function'	=> 'Helper_Authorization_Admin::unauthorize',

		/**
		 * @desc можно перечислить логины, пароли и телефоны пользователей.
		 * Если этот параметр array, то пользователи, не указанные в этом
		 * массиве не могут быть авторизованы этим методом.
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
	 * @desc Дополнительная проверка пользователя перед началом авторизации
	 * до отправки кода СМС.
	 * @param User $user Пользователь.
	 * @param string $email Указанный логин.
	 * @param string $password Указанный пароль.
	 * @return boolean true, если нужно проверять дальше, иначе - false.
	 */
	protected function _prechekUser (User $user, $email, $password)
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
		$email = strtolower ($email);

		return (
			isset ($cfg_users [$email]) &&
			$cfg_users [$email]['password']	== $password &&
			$cfg_users [$email]['phone']	== $user->phone &&
			$cfg_users [$email]['active']
		);
	}

	/**
	 * @desc Дополнительная проверка пользователя перед авторизацией после
	 * проверки кода СМС.
	 * @param User $user Подходящий пользователь.
	 * @param string $email Логин, указанный при авторизации.
	 * @param string $password Пароль.
	 * @return boolean true, если успешно, иначе - false.
	 */
	protected function _postcheckUser (User $user, $email, $password)
	{
		return $this->_prechekUser ($user, $email, $password);
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
	 */
	public function authorize ($data)
	{
		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('email', $data ['email'])
				->where ('password', $data ['password'])
				->where ('md5(`password`)=md5(?)', $data ['password'])
		);

		if (!$user)
		{
			return 'Data_Validator_Authorization_Password/invalid';
		}

		if (!$this->_postcheckUser($user, $data ['email'], $data ['password']))
		{
			return 'Data_Validator_Authorization_User/denied';
		}

		$prefix = $this->config ()->sms_prefix;

		$activation = Model_Manager::byQuery (
			'Activation',
			Query::instance ()
				->where ('code', $prefix . $data ['activation_code'])
				->where ('id', $data ['activation_id'])
				->where ('User__id', $user->id)
				->where ('finished', false)
		);

		if (!$activation || $activation->finished)
		{
			return 'Data_Validator_Activation_Code/invalid';
		}

		$activation->update (array (
			'finished'	=> 1
		));

		return $user->authorize ();
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
		return Data_Validator_Manager::validate ('Email', $login);
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
				->where ('email', $data ['email'])
		);
	}

	/**
	 * @desc Отправляет пользователю СМС для авторизации
	 * @param array $data
	 * @param string $data ['email']
	 * @param string $data ['password']
	 * @param User $data ['user']
	 * @return Activation
	 */
	public function sendActivationSms (array $data)
	{
		$user = $data ['user'];

		if ($user->email != $data ['email'])
		{
			return 'Data_Validator_Authorization_User/unexists';
		}

		if ($user->password != $data ['password'])
		{
			return 'Data_Validator_Authorization_Password/invalid';
		}

		if (!$user->active)
		{
			return 'Data_Validator_Authorization_User/unactive';
		}

		if (!$this->_prechekUser ($user, $data ['email'], $data ['password']))
		{
			return 'Data_Validator_Authorization_User/denied';
		}

		$config = $this->config ();

		$clear_code = Helper_Activation::generateNumeric (
			$config ['code_min_length'],
			$config ['code_max_length']
		);

		$activation_code = $config ['sms_prefix'] . $clear_code;

		$activation = Activation::create (array (
			'address'			=> $user->phone,
			'code'				=> $activation_code,
			'expirationTime'	=>
				Helper_Date::toUnix (time () + $config ['sms_expiration']),
			'User__id'			=> $user->id
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
			$user->phone,
			$user->name,
			array (
				'code'			=> $clear_code,
				'session_id'	=> $activation->id
			),
			$user->id,
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