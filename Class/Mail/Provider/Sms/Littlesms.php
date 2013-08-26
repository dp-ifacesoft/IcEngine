<?php

/**
 * Провайдер отправки сообщений через Littlesms
 *
 * @author goorus, morph
 */
class Mail_Provider_Sms_Littlesms extends Mail_Provider_Abstract
{
	/**
	 * API для работы с LittleSMS
	 *
     * @var LittleSMSoriginal
	 */
	protected $client;

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $config = array (
		'original_path'		=> 'LittlesmsOriginal.class.php',
		'service_login'		=> '',
		'service_password'	=> '',
		'service_sender'	=> 'IcEngine',
		// Кодировка исходного сообщения
		'base_charset'		=> 'utf-8',
		// Кодировка отправляемых сообщений
		'send_charset'		=> 'utf-8'
	);

	/**
	 * (non-PHPdoc)
	 */
	public function __construct()
	{
		$config = $this->config();
		$loader = $this->getService('loader');
		$loader->requireOnce($config['original_path'], 'Vendor');
		$loader->load('LittleSMSoriginal', 'Vendor');
		$this->client = new LittleSMSoriginal(
			$config['service_login'],
			$config['service_password'],
			false
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send(Mail_Message $message, $config)
	{
		$this->logMessage($message, self::MAIL_STATE_SENDING);
		$smsId = $this->sendSms($message->address, $message->body);
		if ($smsId) {
			$this->logMessage(
				$message,
				self::MAIL_STATE_SUCCESS,
				array (
					'sms_id'	=> $smsId
				)
			);
		} else {
			$this->logMessage(
				$message,
				self::MAIL_STATE_FAIL,
				$this->client->getResponse()
			);
		}
		return $smsId;
	}

	/**
	 * Отправка СМС на номер
	 *
     * @param string $phone Номер телефона.
	 * @param string $text Текст сообщения
	 * @return integer|false Номер сообщения, если успешно, иначе false.
	 */
	public function sendSms($phone, $text)
	{
		$this->client->sendSMS(
			$phone,
			iconv(
				$this->config()->base_charset,
				$this->config()->send_charset,
				$text
			),
			$this->config()->service_sender
		);
		$result = $this->client->getResponse();
		if (!empty ($result) && is_array($result) &&
			isset ($result['status']) && $result['status'] == 'success' &&
			isset ($result['messages_id'][0])) {
			return $result['messages_id'][0];
		}
		return false;
	}
}