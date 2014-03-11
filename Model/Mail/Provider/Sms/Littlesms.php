<?php

/**
 *
 * @desc Провайдер отправки сообщений через Littlesms
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Provider_Sms_Littlesms extends Mail_Provider_Abstract
{

	/**
	 * @desc API для работы с LittleSMS
	 * @var LittleSMSoriginal
	 */
	protected $_client;

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
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
	 * @see Model::_afterConstruct()
	 */
	protected function _afterConstruct()
	{
		$config = $this->config();
		Loader::requireOnce($config['original_path'], 'includes');
		Loader::load('LittleSMSoriginal', 'includes');
		$this->_client = new LittleSMSoriginal(
			$config['service_login'],
			$config['service_password'],
			false
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send (Mail_Message $message, $config)
	{
		$this->logMessage ($message, self::MAIL_STATE_SENDING);

		$sms_id = $this->sendSms (
			$message->address,
			$message->body
		);

		if ($sms_id)
		{
			$this->logMessage (
				$message,
				self::MAIL_STATE_SUCCESS,
				array (
					'sms_id'	=> $sms_id
				)
			);
		}
		else
		{
			$this->logMessage (
				$message,
				self::MAIL_STATE_FAIL,
				$this->_client->getResponse ()
			);
		}

		return $sms_id;
	}

	/**
	 * @desc Отправка СМС на номер
	 * @param string $phone Номер телефона.
	 * @param string $text Текст сообщения
	 * @return integer|false Номер сообщения, если успешно, иначе false.
	 */
	public function sendSms ($phone, $text)
	{
		$this->_client->sendSMS (
			$phone,
			iconv (
				$this->config ()->base_charset,
				$this->config ()->send_charset,
				$text
			),
			$this->config ()->service_sender
		);
		$result = $this->_client->getResponse ();

		if (
			!empty ($result) && is_array ($result) &&
			isset ($result ['status']) && $result ['status'] == 'success' &&
			isset ($result ['messages_id'][0])
		)
		{
			return $result['messages_id'][0];
		}

		return false;
	}

	public function getStatus ($smsId)
	{
		$status = $this->_client->checkStatus ($smsId);
		//print_r($status);
		return
			(is_array ($status) && !empty ($status [$smsId])) ?
			array ('sms_status' => $status [$smsId]) :
			null;
	}

}

/*
require_once 'LittleSMS.class.php';

$api = new LittleSMS('vasya', 'qwerty123', true);

// запрос баланса
echo 'Мой баланс: ' . $api->getBalance(), PHP_EOL;

// отправка СМС
echo 'Отправка смс: ' . $api->sendSMS('79631112233', 'Бугагашенька!', 'vasya'), PHP_EOL;

// ответ предыдущего запроса
$response = $api->getResponse();

// запрос статуса сообщения
$result = $api->checkStatus($response['messages_id']);

foreach ($result as $message_id => $status) {
    echo sprintf('Статус сообщения %s: %s', $message_id, $status), PHP_EOL;
}
*/