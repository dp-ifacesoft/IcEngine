<?php

/**
 * Авторизация через отправку пользователю СМС сообщения с кодом.
 *
 * @author goorus, morph
 */
class Authorization_Login_Password_Sms extends Authorization_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected static $config = array(
		// Авторизовать только пользователей, имеющих одну из ролей.
		// Роли перечисляются через запятую.
		'auth_roles_names'			=> 'admin,editor',
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
			'providers'			=> 'Sms_Littlesms,Sms_Dcnk,Sms_Yakoon'
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
	 * Авторизация
	 */
	protected function _authorize(User $user)
	{
		$user->authorize();
		$config = $this->config ();
		if (!$config['authorization_callback']) {
			return;
		}
		list($class, $method) = explode(
            '::', $config['authorization_callback']
		);
		call_user_func(array($class, $method), $user);
	}

	/**
	 * Дополнительная проверка пользователя перед началом авторизации
	 * до отправки кода СМС.
	 *
     * @param User $user Пользователь.
	 * @param string $login Указанный логин.
	 * @param string $password Указанный пароль.
	 * @return boolean true, если нужно проверять дальше, иначе - false.
	 */
	protected function _prechekUser(User $user, $login, $password)
	{
		if (!$this->_userHasRole($user)) {
			return false;
		}
        $userConfig = $this->config()->users;
		if (!$userConfig) {
			return true;
		}
		// Приводим к нижнему регистру
		$login = strtolower($login);
		$config = $userConfig[$login];
		$cryptManager = $this->getService('cryptManager');
		return
			$config &&
			$cryptManager->isMatch($password, $config['password']) &&
			$config['phone'] == $user->phone &&
			$config['active'];
	}

	/**
	 * Дополнительная проверка пользователя перед авторизацией после
	 * проверки кода СМС.
	 *
     * @param User $user Подходящий пользователь.
	 * @param string $login Логин, указанный при авторизации.
	 * @param string $password Пароль.
	 * @return boolean true, если успешно, иначе - false.
	 */
	protected function _postcheckUser(User $user, $login, $password)
	{
		return $this->_prechekUser($user, $login, $password);
	}

	/**
	 * Проверка на принадлежность пользователя к необходимой роли
	 *
     * @param User $user Пользователь
	 * @return boolean
	 */
	protected function _userHasRole(User $user)
	{
		$roles = explode(',', $this->config()->auth_roles_names);
		if (!$roles) {
			// Ролей не задано, авторизуем всех
			return true;
		}
		foreach ($roles as $role) {
			$role = $this->getService('aclRole')->byName($role);
			if ($user->hasRole($role)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function authorize($data)
	{
		$modelManager = $this->getService('modelManager');
		$user = $modelManager->byOptions(
			'User',
			array(
				'name'	=> 'Login',
				'value'	=> $data['login']
			),
			array(
				'name'	=> 'Password',
				'value'	=> $data['password']
			)
		);
		if (!$user) {
			$user = $modelManager->byOptions(
				'User',
				array(
					'name'	=> 'Login',
					'value'	=> $data['login']
				),
				array(
					'name'	=> 'Password',
					'type'	=> 'RSA',
					'value'	=> $data['password']
				)
			);
		}
		if (!$user) {
			return 'Data_Validator_Authorization_Password/invalid';
		}
		if (!$this->_postcheckUser($user, $data['login'], $data['password'])) {
			return 'Data_Validator_Authorization_User/denied';
		}
        $config = $this->config();
        $helperDate = $this->getService('helperDate');
        $activationQuery = $this->getService('query')
            ->select('Activation.id')
            ->from('Activation')
            ->where('type', $config->activation_type)
            ->where('code', $data['activation_code'])
            ->where('id', $data['activation_id'])
            ->where('User__id', $user->key())
            ->where('expirationTime>?', $helperDate->toUnix())
            ->where ('finished<1');
        $activationId = $this->getService('dds')->execute($activationQuery)
            ->getResult()->asValue();
        $activation = $modelManager->byKey('Activation', $activationId);
		if (!$activation) {
			return 'Data_Validator_Activation_Code/invalid';
		}
		$activation->update(array(
			'finished'		=> $activation->finished + 1,
			'finishTime'	=> $helperDate->toUnix()
		));
		$this->_authorize($user);
		return $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered($login)
	{
		$user = $this->getService('modelManager')->byOptions(
			'User',
			array(
                'name'  => 'Login',
                'value' => $login
            )
		);
		return (bool) $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin($login)
	{
		return $this->getService('dataValidatorManager')->validate(
			$this->config()->login_validator, $login
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser($data)
	{
		$user = $this->getService('modelManager')->byOptions(
			'User',
			array(
                'name'  => 'Login',
                'value' => $data['login']
            )
		);
        return $user;
	}

    /**
     * Сгенерировать код активации
     *
     * @param int $minLength
     * @param int $maxLength
     * @return string
     */
    public function genCode($minLength = 5, $maxLength = 7)
	{
        $code = (string) rand(
			str_pad("1", $minLength, '0'),	// от 10000
			str_repeat('9', $maxLength)		// до 9999999
		);
		return $code;
	}

	/**
	 * Отправляет пользователю СМС для авторизации
	 *
     * @param array $data
	 * @param string $data ['login']
	 * @param string $data ['password']
	 * @param User $data ['user']
	 * @return Activation
	 */
	public function sendActivationSms(array $data)
	{
		$user = $data['user'];
		if (strcasecmp($user->login, $data['login']) != 0) {
			return 'Data_Validator_Authorization_User/unexists';
		}
		if ($user->password != $data ['password'] &&
			$user->password != md5($data['password'])) {
			return 'Data_Validator_Authorization_Password/invalid';
		}
		if (!$user->active) {
			return 'Data_Validator_Authorization_User/unactive';
		}
		if (!$this->_prechekUser($user, $data['login'], $data['password'])) {
			return 'Data_Validator_Authorization_User/denied';
		}
		$config = $this->config ();
		$activationCode = $this->genCode(
            $config['code_min_length'],
            $config['code_max_length']
        );
		$modelManager = $this->getService('modelManager');
        $helperDate = $this->getService('helperDate');
		$activationQuery = $this->getService('query')
            ->select('Activation.id')
            ->from('Activation')
            ->where('User__id', $user->key())
            ->where('address', $user->phone)
            ->where('finished<0')
            ->where('type', $config['activation_type'])
            ->where('expirationTime>?', $helperDate->toUnix());
        $activationId = $this->getService('dds')->execute($activationQuery)
            ->getResult()->asValue();
        $activation = $modelManager->byKey('Activation', $activationId);
		if ($activation) {
			// За каждое повторное использование, приближаем к финишу,
			// чтобы если первая СМС не дошла, можно было добиться повторной
			// отправки.
			$activation->update(array (
				'finished'	=> $activation->finished + 1
			));
			$activationCode = $activation->code;
		} else {
			$expTime = time() + $config['sms_expiration'];
			$activation = $this->getService('activation')->create(array(
				'finished'			=> -2,
				'address'			=> $user->phone,
				'type'				=> $config['activation_type'],
				'code'				=> $activationCode,
				'expirationTime'	=> $helperDate->toUnix($expTime),
				'User__id'			=> $user->key()
			));
		}
		/**
		 * Провайдер
		 *
         * @var Mail_Provider_Abstract
		 */
        $providerName = !empty($data['provider'])
            ? $data['provider'] : $config['sms_provider'];
		$mailMessage = $this->getService('mailMessage');
        $dto = $this->getService('dto')->newInstance('Mail_Message')
            ->setTemplate($config['sms_mail_template'])
            ->setAddress($user->phone)
            ->setToName($user->title())
            ->setData(array(
                'code'          => $activationCode,
                'session_id'    => $activation->key()
            ))
            ->setToUserId($user->key())
            ->setMailProviderParams($config['sms_provider_params']->__toArray())
            ->setMailProvider($providerName);
        $message = $mailMessage->create($dto)->save();
		if ($config['sms_test_mode']) {
			echo 'sms test mode';
		} else {
			$message->send();
		}
		return $activation;
	}
}