<?php

/**
 * Отправка sms через dc-nk.ru
 * 
 * @author goorus, morph
 */
class Mail_Provider_Sms_Dcnk extends Mail_Provider_Abstract
{

	/**
	 * @inheritdoc
	 */
	protected $config = array (
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
	 * SOAP клиент
	 * 
     * @var yakoon_soapclient
	 */
	protected $client;

	/**
	 * @inheritdoc
	 */
	public function __construct()
	{
		$loader = $this->getService('loader');
		$loader->requireOnce($this->config()->nusoap_path, 'Vendor');
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$this->client = new yakoon_soapclient(
			$this->config()->msg_gate_url . '?WSDL',
			false,
			$proxyhost, $proxyport,
			$proxyusername, $proxypassword
		);
	}

	/**
	 * @inheritdoc
	 */
	public function send(Mail_Message $message, $config)
	{
		$this->logMessage($message, self::MAIL_STATE_SENDING);
		$thisConfig = $this->config();
		$params = array(
			'msguser'		=> $thisConfig['msguser'],
			'password'		=> $thisConfig['password'],
			'text'			=> iconv(
                $thisConfig['base_charset'],
				$thisConfig['send_charset'],
				$message->body
			),
			'dtsend'		=> !empty($config ['date'])
                ? $config['date'] 
                : date('d.m.Y H:i'),
			'grpid'			=> '0',
			'abid'			=> '0',
			'phone'			=> $message->address,
			'email'			=> $message->address,
			'istranslit'	=> true,
			'isdelivery'	=> true
		);
		$result = $this->client->call(
			'SendTextMessage', $params, $thisConfig['msg_gate_url'],
			'SendTextMessage'
		);
		$err = $this->client->getError();
		$this->logMessage(
			$message,
			empty($err) ? self::MAIL_STATE_SUCCESS : self::MAIL_STATE_FAIL,
			var_export($result, true)
		);
		return empty($err);
	}
}