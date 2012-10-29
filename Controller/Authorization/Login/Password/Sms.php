<?php
/**
 *
 * @desc Контроллер для авторизации по емейлу, паролю и смс.
 * Предназначен для авторизации контентов в админке, поэтому
 * сверяет данные из БД с данными из файла конфига.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Authorization_Login_Password_Sms extends Controller_Abstract
{

	/**
	 * @param Аттрибут с кодом, высланным в СМС
	 * @var string
	 */
	const SMS_CODE_ATTR = 'smsAuthCode';

	/**
	 * @param Аттрибут - количество отправленных СМС
	 * @var string
	 */
	const SMS_SEND_COUNTER_ATTR = 'smsAuthSendCount';

	/**
	 * @param Аттрибут со временем последней отправки кода
	 * @var string
	 */
	const SMS_SEND_TIME_ATTR = 'smsAuthSendTime';

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array (
		// Лимит смс в 1 минуту
		'sms_send_limit_1m'			=> 60,

		// Лимит смс на 10 минут
		'sms_send_limit_10m'		=> 190
	);

	/**
	 * @desc Вовзращает модель авторизации.
	 * @return Authorization_Login_Password_Sms
	 */
	protected function _authorization ()
	{
		return Model_Manager::byQuery (
			'Authorization',
			Query::instance ()
				->where ('name', 'Login_Password_Sms')
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		// Просто форма авторизации
	}

	/**
	 * @desc Авторизация
	 * @param string $name Емейл пользователя
	 * @param string $pass Пароль
	 * @param string $code Код активации из СМС
	 */
	public function login ()
	{
		list (
			$login,
			$password,
			$activation_id,
			$activation_code,
			$redirect
		) = $this->_input->receive (
			'name',
			'pass',
			'a_id',
			'code',
			'href'
		);

		if (!$activation_id && $activation_code)
		{
			// Сразу указали код активации, мб старая активация
			$activation = Model_Manager::byQuery (
				'Activation',
				Query::instance ()
					->from ('Activation')
					->singleInnerJoin (
						'User',
						'Activation.User__id=User.id'
					)
					->where ('Activation.code', $activation_code)
					->where ('User.login', $login)
					->where (
						'(
							md5(User.password)=md5(?) OR
							User.password=md5(?)
						)',
						array ($password, $password)
					)
			);

			if ($activation)
			{
				$activation_id = $activation->id;
			}
		}

		if (!$activation_id || !$activation_code)
		{
			return $this->replaceAction ($this, 'sendSmsCode');
		}

		$user = $this->_authorization ()->authorize (array (
			'login'				=> $login,
			'password'			=> $password,
			'activation_id'		=> $activation_id,
			'activation_code'	=> $activation_code
		));

		if (!is_object ($user))
		{
			// Пользователя не существует
			$this->_sendError (
				'authorization error: ' . $user,
				$user ? $user : __METHOD__,
				$user ? null : '/passwordIncorrect'
			);
			return ;
		}

		// Сбрасываем счетчик СМС.
		$user->attr (array (
			self::SMS_SEND_COUNTER_ATTR	=> 0,
			self::SMS_CODE_ATTR			=> ''
		));

		$redirect = Helper_Uri::validRedirect ($redirect);
		$this->_output->send (array (
			'redirect'		=> $redirect,
			'data'	=> array (
				'redirect'	=> $redirect
			)
		));
	}

	/**
	 * @desc Отправка СМС кода
	 */
	public function sendSmsCode ()
	{
		list(
			$provider,
			$login,
			$password,
			$send
		) = $this->_input->receive(
			'provider',
			'name',
			'pass',
			'send'
		);
		$user = Model_Manager::byOptions(
			'User',
			array(
				'name'	=> 'Login',
				'value'	=> $login
			),
			array(
				'name'	=> 'Password',
				'value'	=> $password
			)
		);
		if (!$user) {
			$user = Model_Manager::byOptions (
				'User',
				array(
					'name'	=> 'Login',
					'value'	=> $login
				),
				array(
					'name'	=> 'Password',
					'type'	=> 'RSA',
					'value'	=> $password
				)
			);
		}
		if (!$user) {
			return $this->_sendError(
				'password incorrect',
				'Data_Validator_Authorization_Password/invalid'
			);
		}

		if (!$user->active) {
			return $this->_sendError(
				'user unactive',
				'Data_Validator_Authorization_User/unactive'
			);
		}

		if (!$user->phone) {
			return $this->_sendError('noPhone');
		}

		$count = $user->attr (self::SMS_SEND_COUNTER_ATTR);
		$time = Helper_Date::toUnix ();
		$last_time = $user->attr (self::SMS_SEND_TIME_ATTR);
		$delta_time = Helper_Date::secondsBetween ($last_time);

		if (
			(
				$count >= $this->config ()->sms_send_limit_1m &&
				$delta_time < 60
			) ||
			(
				$count >= $this->config ()->sms_send_limit_10m &&
				$delta_time < 600
			)
		)
		{
			return $this->_sendError ('smsLimit');
		}

		$activation = $this->_authorization ()->sendActivationSms (array (
			'login'		=> $login,
			'password'	=> $password,
			'phone'		=> $user->phone,
			'user'		=> $user,
			'provider'	=> $provider,
			'send'		=> $send
		));

		if (!is_object ($activation))
		{
			$this->_sendError (
				'send activation code fail (' . (string) $activation . ')',
				$activation ? $activation : 'accessDenied'
			);
			return ;
		}

		$user->attr (array (
			self::SMS_SEND_TIME_ATTR		=> $time,
			self::SMS_SEND_COUNTER_ATTR		=> $count + 1
		));

		$this->_output->send (array (
			'activation'	=> $activation,
			'time'			=> $time,
			'data'			=> array (
				'activation_id'		=> $activation->id
			)
		));
	}

}