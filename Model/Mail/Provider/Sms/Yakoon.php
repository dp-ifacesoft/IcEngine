<?php

/**
 *
 * @desc Провайдер сообщений через сервис Yakoon.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Provider_Sms_Yakoon extends Mail_Provider_Abstract
{

	/**
	 * @desc Признак успешной отправки СМС
	 * @var string
	 */
	const SEND_SMS_OK = '3100';

	/**
	 * @desc Признак успешного получения статуса СМС
	 * @var string
	 */
	const GET_STATUS_OK = '3200';

	/**
	 * @desc SOAP клиент
	 * @var yakoon_soapclient
	 */
	protected $_client;

	/**
	 * @desc Последний ответ сервера якун
	 * @var string
	 */
	protected $_lastAnswer = '';

	/**
	 * @desc Последий код результата отправки смс сообщения.
	 * @var integer
	 */
	protected $_lastResultCode = 0;

	/**
	 * @desc Расшифровка кода результата отправки
	 * @var array
	 */
	protected $_resultCodes = array (
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
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $config = array (
		'nusoap_path'		=> 'sms/nusoap.php',
		'service_url'		=> 'http://sms.yakoon.com/sms.asmx?wsdl',
		'service_login'		=> '',
		'service_password'	=> '',
		'service_sender'	=> 'IcEngine',
		// Исходная кодировка сообщений
//		'base_charset'		=> 'utf-8',
		// Кодировка отправляемых сообщений
//		'send_charset'		=> 'utf-8'
	);

	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{
		$loader = $this->getService('loader');
		$loader->requireOnce ($this->config ()->nusoap_path, 'includes');

		$this->_client = new yakoon_soapclient (
			$this->config ()->service_url,
			'wsdl'
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send (Mail_Message $message, $config)
	{
		$sms_id = $this->sendSms ($message->address, $message->body);

		if ($sms_id)
		{
			$this->logMessage (
				$message,
				self::MAIL_STATE_SUCCESS,
				array (
					'sms_id'	=> $sms_id,
					'answer'	=> $this->_lastAnswer,
					'code'		=> $this->_lastResultCode
				)
			);
		}
		else
		{
			$code = $this->_lastResultCode;
			$this->logMessage (
				$message,
				self::MAIL_STATE_FAIL,
				array (
					'sms_id'	=> $sms_id,
					'answer'	=> $this->_lastAnswer,
					'code'		=> $code,
					'error'		=>
						isset ($this->_resultCodes [$code]) ?
							$this->_resultCodes [$code] :
							''
				)
			);
		}

		return (int) $sms_id;
	}

	/**
	 * @desc отправка сообщения
	 * @param string $phone Телефон
	 * @param string $text Сообщение
	 * @return integer|false Id отправленного сообщения или false.
	 */
	function sendSms ($phone, $text)
	{
		$config = $this->config ();
		
		//$_classes['common']->UpdateTableRecordInfoByIdValue('sms', 'sms_result', $sms['id'], 'sending');
		//$_classes['common']->UpdateTableRecordInfoByIdValue('sms', 'provider', $sms['id'], 'yakoon');
		$sms_result = $this->_client->call (
			'Send',
			array (
				'Username'		=> $config ['service_login'],
				'Password'		=> md5 ($config ['service_password']),
				'Sender'		=> $config ['service_sender'],
				'Recipient'		=> $phone,
				'Template'		=> '',
				'Content'		=> Helper_Translit::rus2translit ($text),
//					iconv (
//						$this->config ()->base_charset,
//						$this->config ()->send_charset,
//						$text
//					),
				'Format'		=> '1',
				'SendOn'		=> 120,
				'Notification'	=> '1'
			)
		);

		$sms_result = explode (';', $sms_result);

		$this->_lastResultCode = (int) $sms_result [0];

		if (
			$this->_lastResultCode != self::SEND_SMS_OK ||
			count ($sms_result) < 2
		)
		{
			return false;
		}
		$sms_id = (int) $sms_result [1];

		return $sms_id;
		//return array('sms_result' => $sms_result, 'sms_id' => $sms_id);
		/*
		$_classes['common']->UpdateTableRecordInfoByIdValue('sms', 'sms_result', $sms['id'], $sms_result);
		if ($sms_id)
		{
			$_classes['common']->UpdateTableRecordInfoByIdValue('sms', 'sms_id', $sms['id'], $sms_id);
		}
		*/
	}

	/**
	 * @desc Получение статуса СМС.
	 * @param string $sms_id
	 */
	function getStatus ($sms_id)
	{
		$config = $this->config ();

		$sms_status = $this->_client->call (
			'Status',
			array (
				'Username'	=> $config ['service_login'],
				'Password'	=> md5 ($config ['service_password']),
				'IDSms'		=> $sms_id,
				'IDInt'		=> ''
			)
		);

		$this->_lastAnswer = $sms_status;
		$this->_lastResultCode = (int) $sms_status;


		if (strpos ($sms_status, '3200') === false)
		{
			return $sms_status;
		}

		//echo $sms_status . ' - asked OK, ';
		$sms_answer = substr ($sms_status, strpos ($sms_status, ':') + 1);
		//echo '$sms_answer = ' . $sms_answer . '; ';
		$sms_answers = explode (';', $sms_answer);
		if (is_array ($sms_answers))
		{
			$sms_answer = $sms_answers [0];
		}
		$sms_status_code = substr ($sms_answer, strrpos ($sms_answer, ',') + 1);
		$sms_status = $sms_status_code;
		switch ($sms_status)
		{
			case '1':
				$sms_status = 'Waiting';
				break;
			case '2':
				$sms_status = 'Processing';
				break;
			case '3':
				$sms_status = 'Scheduled';
				break;
			case '4':
				$sms_status = 'Canceled';
				break;
			case '5':
				$sms_status = 'Failed';
				break;
			case '6':
				$sms_status = 'Routing error';
				break;
			case '7':
				$sms_status = 'Credit';
				break;
			case '8':
				$sms_status = 'Unknown';
				break;
			case '9':
				$sms_status = 'Delivered';
				break;
		}

		$sms_result = 'OK_Operation_Completed';
		$sms_id = substr ($sms_result, strpos ($sms_result, ';') + 1);
		//echo '- ' . $sms_status . ' -';
		return array (
			'sms_result' => $sms_result,
			'sms_status' => $sms_status
		);
	}
}