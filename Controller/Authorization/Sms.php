<?php
/**
 *
 * @desc Контроллер авторизации
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Authorization_Sms extends Controller_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	public $config = array (
		// Возможно авторизация через СМС.
		'sms_auth_enable'			=> false,
		// Префикс активации при авторизации через СМС.
		// Этот префикс не будет отсылаться в сообщении, но необходим
		// для уникальности ключей активации.
		'sms_auth_prefix'			=> 'sms_auth.',
		// Время действительности кода активации - 1 час.
		'sms_auth_expiration'		=> 3600,
		// Шаблон смски.
		'sms_auth_mail_template'	=> 'sms_activate',
		// Провайдер, отправляющий СМС
		'sms_auth_provider'			=> 'First_Success',
		// Параметры СМС провайдера
		'sms_auth_provider_config'	=> array (
			'providers'	=> 'Sms_Dcnk,Sms_Littlesms,Sms_Yakoon'
		),
		// Режим тестирования СМС (сообщения не отправляются и код отправляется
		// в ответ на POST запрос отправки СМС)
		'sms_auth_test_mode'		=> false,
		// Авторегистрация
		'sms_auto_registration'		=> false
	);

	/**
	 * @desc Возвращает адрес для редиректа
	 * @return string
	 */
	protected function _redirect ()
	{
		$redirect = $this->_input->receive ('redirect');
		return Helper_Uri::validRedirect (
			$redirect ?
				$redirect :
				self::DEFAULT_REDIRECT
		);
	}

	/**
	 * @desc Авторизация
	 */
	public function login ()
	{
		if (!$this->config ()->sms_auth_enable)
		{
			$this->_output->send ('error', 'Sms auth disabled');
			$this->_task->setClassTpl (__METHOD__, 'fail');
			return;
		}

		list (
			$phone,
			$activation_id,
			$clear_code
		) = $this->_input->receive (
			'phone',
			'sms_session_id',
			'sms_session_code'
		);

		$phone = Helper_Phone::parseMobile ($phone);
		$code = $this->config ()->sms_auth_prefix . $clear_code;

		$activation = Model_Manager::byKey ('Activation', $activation_id);

		if (!$activation || $activation->code != $code)
		{
			$this->_sendError ('incorrect code', 'incorrect_code');
			return;
		}

		if ($activation->finished)
		{
			$this->_sendError ('expired', 'expired');
			return ;
		}


		$exp = Helper_Date::cmpUnix (
			date ('Y-m-d H:i:s'),
			$activation->expirationTime
		);

		if ($exp > 0)
		{
			$this->_sendError ('expired', 'expired');
			return ;
		}

		// Можно авторизовать
		/**
		 * @var User $user
		 */
		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('phone', $phone)
		);

		if (!$user)
		{
			// Пользователья не существует
			return $this->_sendError ('user not found', 'userNotFound');
		}

		// пользователь зарегистрирован, авторизуем
		$user->authorize ();
		$this->_output->send ('data', array (
			'user'	=> array (
				'id'	=> $user->id,
				'name'	=> $user->name
			),
			'redirect'	=> $this->_redirect ()
		));
	}

	/**
	 * @desc Авторизация или регистрация через СМС
	 * @param string $phone Номер телефона
	 * @param string $sms_session_id Id активации.
	 * @param string $sms_session_code Код, пришедший по смс.
	 */
	public function loginOrReg ()
	{
		$phone = Helper_Phone::parseMobile ($this->_input->receive ('phone'));

		/**
		 * @var User $user
		 */
		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
			->where ('phone', $phone)
		);

		if (!$user)
		{
			// Пользователья не существует
			return $this->replaceAction ($this, 'register');
		}

		return $this->replaceAction ($this, 'login');
	}

	/**
	 * @desc Регистрация по номеру телефона
	 */
	public function register ()
	{
		if (!$this->config ()->sms_auto_registration)
		{
			$this->_sendError (
				'registration disabled',
				__METHOD__,
				'incorrect_code'
			);
			return ;
		}

		$phone = Helper_Phone::parseMobile ($this->_input->receive ('phone'));

		$user = new User (array (
			'name'		=> $phone,
			'email'		=> '',
			'password'	=> '',
			'active'	=> 1,
			'ip'		=> Request::ip (),
			'phone'		=> $phone
		));

		$user->save ();

		// пользователь зарегистрирован, авторизуем
		$user->authorize ();
		$this->_output->send ('data', array (
			'user'	=> array (
				'id'	=> $user->id,
				'name'	=> $user->name
			),
			'redirect'	=> $this->_redirect ()
		));
	}

}