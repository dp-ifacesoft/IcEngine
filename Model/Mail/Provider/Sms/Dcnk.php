<?php

/**
 *
 * @desc Отправка sms через dc-nk.ru
 * @author Юрий Шведов
 * @package Ice_Vipgeo
 *
 */
class Mail_Provider_Sms_Dcnk extends Mail_Provider_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $config = array (
		// Путь до клиента
		'nusoap_path'	=> 'sms/nusoap.php',
		// Логин
		'msguser'		=> '',
		// Пароль сервиса
		'password'		=> '',
		// адрес сервиса
		'msg_gate_url'	=> 'http://www.dc-nk.ru/service/msggate/msgservice.php',
		// базовая кодировка сообщения
		'base_charset'	=> 'utf-8',
		// кодировка отправляемых сообщений
		'send_charset'	=> 'utf-8'
	);

	/**
	 * @desc SOAP клиент
	 * @var yakoon_soapclient
	 */
	protected $_client;

	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	public function _afterConstruct ()
	{
		$loader = $this->getService('loader');
		$loader->requireOnce ($this->config ()->nusoap_path, 'includes');

		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';

		$this->_client = new yakoon_soapclient (
			$this->config ()->msg_gate_url . '?WSDL',
			false,
			$proxyhost, $proxyport,
			$proxyusername, $proxypassword
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send (Mail_Message $message, $config)
	//function sendTextMessage($phone, $email, $date, $text )
	{
		$this->logMessage ($message, self::MAIL_STATE_SENDING);

		$result = null;
		//echo $date . "<br>";

		$this_config = $this->config ();

		$params = array (
			'msguser'		=> $this_config ['msguser'],
			'password'		=> $this_config ['password'],
			'text'			=>
				iconv (
					$this_config ['base_charset'],
					$this_config ['send_charset'],
					$message->body
				),
			'dtsend'		=>
				!empty ($config ['date']) ?
				$config ['date'] :
				date ("d.m.Y H:i"),
			'grpid'			=> '0',
			'abid'			=> '0',
			'phone'			=> $message->address,
			'email'			=> $message->address,
			'istranslit'	=> true,
			'isdelivery'	=> true
		);

		$result = $this->_client->call (
			'SendTextMessage',
			$params,
			$this_config ['msg_gate_url'],
			'SendTextMessage'
		);
		$err = $this->_client->getError ();

		$this->logMessage (
			$message,
			empty ($err) ? self::MAIL_STATE_SUCCESS : self::MAIL_STATE_FAIL,
			var_export ($result, true)
		);

		return empty ($err);
	}

	/**
	 * @desc Получает и возвращает состояние сообщения
	 * @param string $message_id Id сообщения.
	 * @return string Состояние
	 */
	function getStatus ($message_id)
	{
		$config = $this->config ();

		$params = array
		(
			'msguser'		=> $config ['msguser'],
			'password'		=> $config ['password'],
			'messageid'		=> $message_id
		);

		$result = $this->_client->call (
			'GetMessageState',
			$params,
			$config ['msg_gate_url'],
			'GetMessageState'
		);

//		$params = array
//		(
//			'msguser' => 'forguest',
//			'password' => '123456',
//			'messageid'  => $message_id
//		);
//
//		$result = $this->_client->call('GetMessageState', $params, 'http://www.dc-nk.ru/service/msggate/msgservice.php', 'GetMessageState');
		return $result;
	}

}
