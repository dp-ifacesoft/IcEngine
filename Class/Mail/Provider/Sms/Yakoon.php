<?php

/**
 * Провайдер сообщений через сервис Yakoon.
 * 
 * @author goorus, morph
 */
class Mail_Provider_Sms_Yakoon extends Mail_Provider_Abstract
{
	/**
	 * Признак успешной отправки СМС
	 * 
     * @var string
	 */
	const SEND_SMS_OK = '3100';

	/**
	 * Признак успешного получения статуса СМС
	 * 
     * @var string
	 */
	const GET_STATUS_OK = '3200';

	/**
	 * SOAP клиент
	 * 
     * @var yakoon_soapclient
	 */
	protected $client;

	/**
	 * Последний ответ сервера якун
	 * 
     * @var string
	 */
	protected $lastAnswer = '';

	/**
	 * Последий код результата отправки смс сообщения.
	 * 
     * @var integer
	 */
	protected $lastResultCode = 0;

	/**
	 * Расшифровка кода результата отправки
	 * 
     * @var array
	 */
	protected $resultCodes = array(
		// Отправка СМС
		'3101'	=> 'Wrong data submitted: Username',
		'3102'	=> 'Wrong data submitted: Password',
		'3116'	=> 'Wrong data submitted: SenderID',
		'3117'	=> 'Wrong data submitted: Recipients',
		'3118'	=> 'Wrong data submitted: Template',
		'3119'	=> 'Wrong data submitted: Content',
		'3120'	=> 'Wrong data submitted: Format',
		'3121'	=> 'Wrong data submitted: dateSendOn',
		'3122'	=> 'Wrong data submitted: Notification',
		'3151'	=> 'Other database error',
		'3153'	=> 'Access denied: Account is not activated',
		'3176'	=> 'No credits: To send message',
		'3181'	=> 'Not exist: Recipients',
		'3199'	=> 'IP blocked',
		'3100'	=> 'OK_Operation_Completed',
		// Запрос статуса СМС
		'3201'	=> 'Wrong data submitted: Username',
		'3202'	=> 'Wrong data submitted: Password',
		'3223'	=> 'Wrong data submitted: IDSms',
		'3224'	=> 'Wrong data submitted: IDInt',
		'3251'	=> 'Other database error',
		'3253'	=> 'Access denied: Account is not activated',
		'3284'	=> 'Not exist: SMS to status',
		'3299'	=> 'IP blocked',
		'3200'	=> 'OK_Operation_Completed'
	);

	/**
	 * @inheritdoc
	 */
	protected $сonfig = array(
		'nusoap_path'		=> 'sms/nusoap.php',
		'service_url'		=> 'http://sms.yakoon.com/sms.asmx?wsdl',
		'service_login'		=> '',
		'service_password'	=> '',
		'service_sender'	=> 'IcEngine'
	);

	/**
	 * (non-PHPdoc)
	 */
	protected function __construct()
	{
		$loader = $this->getService('loader');
		$loader->requireOnce($this->config()->nusoap_path, 'Vendor');
		$this->_client = new yakoon_soapclient(
			$this->config()->service_url, 'wsdl'
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send(Mail_Message $message, $config)
	{
		$smsId = $this->sendSms($message->address, $message->body);
		if ($smsId) {
			$this->logMessage(
				$message,
				self::MAIL_STATE_SUCCESS,
				array(
					'sms_id'	=> $sms_id,
					'answer'	=> $this->_lastAnswer,
					'code'		=> $this->_lastResultCode
				)
			);
		} else {
			$code = $this->lastResultCode;
			$this->logMessage(
				$message,
				self::MAIL_STATE_FAIL,
				array(
					'sms_id'	=> $smsId,
					'answer'	=> $this->lastAnswer,
					'code'		=> $code,
					'error'		=> isset($this->resultCodes[$code]) 
                        ? $this->resultCodes[$code] 
                        : ''
				)
			);
		}
		return (int) $smsId;
	}

	/**
	 * Отправка сообщения
	 * 
     * @param string $phone Телефон
	 * @param string $text Сообщение
	 * @return integer|false Id отправленного сообщения или false.
	 */
	function sendSms($phone, $text)
	{
		$config = $this->config();
		$preSmsResult = $this->client->call(
			'Send',
			array (
				'Username'		=> $config['service_login'],
				'Password'		=> md5($config['service_password']),
				'Sender'		=> $config['service_sender'],
				'Recipient'		=> $phone,
				'Template'		=> '',
				'Content'		=> $this->getService('helperMetagraphy')
                    ->rus2translit($text),
				'Format'		=> '1',
				'SendOn'		=> 120,
				'Notification'	=> '1'
			)
		);
		$smsResult = explode(';', $preSmsResult);
		$this->lastResultCode = (int) $smsResult[0];
		if ($this->lastResultCode != self::SEND_SMS_OK ||
			count($smsResult) < 2) {
			return false;
		}
		$smsId = (int) $smsResult[1];
		return $smsId;
	}
}