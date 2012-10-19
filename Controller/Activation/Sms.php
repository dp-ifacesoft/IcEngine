<?php
/**
 *
 * @desc Контроллер активации по СМС.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Activation_Sms extends Controller_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	public $config = array (
		// Включено
		'enable'		=> false,
		// Типы активация
		'code_types'	=> array (
			// Активация логина (авторизация/регистрация)
			'phone_login'	=> array (
				// Время активности (в секундах)
				'expiration'		=> 3600,
				// Колбэк при после активации
				'callback'			=> '',
				// Провайдер
				'provider'			=> 'First_Success',
				// Конфиг для провайдера
				'provider_config'	=> array (
					'providers'	=> 'Sms_Dcnk'//,Sms_Littlesms,Sms_Yakoon'
				),
				// Шаблон СМС
				'mail_template'		=> 'sms_activate',
				// Префикс кода
				'prefix'			=> 'phone.',
				// Минимальное кол-во символов
				'from'				=> 5,
				// Максимальное кол-во символов
				'to'				=> 7
			)
		)
	);

	/**
	 * @desc Создание активации с коротким кодом
	 * @param array $params
	 */
	protected function _newActivationShort (array $params)
	{

	}

	/**
	 * @desc Отправка кода активации
	 */
	public function sendCode ()
	{
		list (
			$phone,
			$code_type
		) = $this->_input->receive (
			'phone',
			'code_type'
		);

		if (!$phone || !$code_type)
		{
			$this->_sendError (
				'empty phone or code_type',
				__METHOD__,
				'/fail'
			);
			return;
		}

		$type = $this->config ()->code_types [$code_type];

		if (!$type)
		{
			$this->_sendError (
				'no type data',
				__METHOD__,
				'/fail'
			);
			return;
		}

		$code = Helper_Activation::newShortCode (
			$type ['prefix'],
			$type ['from'],
			$type ['to']
		);

		if (!$code)
		{
			$this->_sendError (
				'error on activation create',
				__METHOD__,
				'/fail'
			);
			return;
		}

		$activation = Activation::create (array (
			'code'				=> $code,
			'expirationTime'	=> Helper_Date::toUnix (time () + $type ['expiration_time']),
			'callbackMessage'	=> $type ['callback'] ? $type ['callback'] : ''
		));

		if (!$activation)
		{
			$this->_sendError (
				'error on activation create',
				__METHOD__,
				'/fail'
			);
			return;
		}

		$provider_name = $type->provider;

		/**
		 * @desc Провайдер
		 * @var Mail_Provider_Abstract
		 */
		$provider = Model_Manager::byQuery (
			'Mail_Provider',
			Query::instance ()
			->where ('name', $provider_name)
		);

		if (!$provider)
		{
			$this->_sendError (
				'provider not found: ' . $provider_name,
				'fail'
			);
			return;
		}
		
		$message = Mail_Message::create (
			$type ['mail_template'],
			$phone, $phone,
			array (
				'code'			=> substr ($code, strlen ($type ['prefix'])),
				'session_id'	=> $activation->id
			),
			User::id (),
			$provider->id,
			$type ['provider_config']->__toArray ()
		)->save ();

		if (!$message->send ())
		{
			$this->_sendError (
				'mail send error',
				__METHOD__,
				'/fail'
			);
			return;
		}

		$this->_output->send ('activation', $activation);
	}

}