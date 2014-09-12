<?php

/**
 * Провайдер отправки сообщений через deshevle.sms.ru
 *
 * @author LiverEnemy
 */
class Mail_Provider_Sms_Smsru extends Mail_Provider_Abstract
{
	/**
	 * API для работы с deshevle.sms.ru
	 *
     * @var smsru
	 */
	protected $client;

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $config = array (
		'original_path'		=> 'smsru.php',
		'api_id'        	=> '2cbb2609-fd64-e894-f15c-a0bf129fe960',
		'service_sender'	=> null,//'IcEngine',
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
		$loader->load('smsru', 'Vendor');
		$this->client = new smsru(
			$config['api_id']
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send(Mail_Message $message, $config)
	{
		$this->logMessage($message, self::MAIL_STATE_SENDING);
		$smsResult = $this->sendSms($message->address, $message->body);
		if ($smsResult && !empty($smsResult['code']) && $smsResult['code'] == '100') {
            $smsId = $smsResult['ids'][0];
			$this->logMessage(
				$message,
				self::MAIL_STATE_SUCCESS,
				array (
					'sms_id'	=> $smsResult['ids'][0],
                    'balance'   => $smsResult['balance'],
				)
			);
            return $smsId;
		} elseif (isset($smsResult['description'])) {
			$this->logMessage(
				$message,
				self::MAIL_STATE_FAIL,
				array(
                    'description'   => $smsResult['description']
                )
			);
		} else {
            $this->logMessage(
                $message,
                self::MAIL_STATE_FAIL,
                NULL
            );
        }
		return null;
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
		$smsResult = $this->client->sms_send(
			$phone,
			iconv(
				$this->config()->base_charset,
				$this->config()->send_charset,
				$text
			),
			$this->config()->service_sender,
            NULL,
            FALSE,
            FALSE,
            1568
		);
        if ($smsResult && is_array($smsResult) && !empty($smsResult['code'])/* && $smsResult['code'] == '100'*/)
        {
            return $smsResult;
        }
		return false;
	}
}