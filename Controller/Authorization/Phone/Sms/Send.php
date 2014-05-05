<?php
/**
 *
 * @desc Контроллер для авторизации по номеру телефона и коду,
 * высылаемому сервером по смс.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Authorization_Phone_Sms_Send extends Controller_Authorization_Abstract
{

	/**
	 * @desc Авторегистрация по СМС
	 */
	public function autoregister ()
	{
		if (!$this->config ()->autoreg_enable)
		{
			$this->_sendError (
				'disabled',
				__METHOD__,
				'/fail'
			);
			return ;
		}

		$prefix = $this->config ()->fields_prefix;
		$phone = $this->_input->receive ($prefix . 'login');

		$phone = Helper_Phone::parseMobile ($phone);

		$user = User::create (array (
			'name'		=> Helper_Phone::formatMobile ($phone),
			'email'		=> '',
			'password'	=> md5 (time ()),
			'phone'		=> $phone,
			'active'	=> 1
		))->authorize ();

		$this->_output->send (array (
			'user'	=> $user,
			'data'	=> array (
				'user_id'	=> $user->id
			)
		));
	}

	/**
	 * @desc Авторизация через отправку СМС с кодом
	 * @param string $phone Номер телефона для отправки СМС
	 */
	public function sendSmsCode ()
	{
		$phone = Helper_Phone::parseMobile ($this->_input->receive ('phone'));

		if (!$phone)
		{
			$this->_sendError (
				'empty phone or code_type',
				__METHOD__,
				'/fail'
			);
			return ;
		}

		$activation = $this->_authorization ()->sendActivationSms (array (
			'phone'	=> $phone
		));

		if (!$activation)
		{
			$this->_sendError (
				'fail',
				__METHOD__,
				'/fail'
			);
		}

		$cfg = $this->_authorization ()->config ();

		$this->_output->send (array (
			'activation'	=> $activation,
			'data'			=> array (
				'activation_id'		=> $activation->id,
				'phone_registered'	=> (bool) $activation->User__id,
				'code'				=>
						$cfg->sms_test_mode ?
						substr ($activation->code, strlen ($cfg->sms_prefix)) :
						''
			)
		));
	}

}