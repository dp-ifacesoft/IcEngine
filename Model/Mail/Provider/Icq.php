<?php
/**
 *
 * @desc Провайдер для отправки сообщений по Icq.
 * @author Колесников Илья
 * @package IcEngine
 *
 */

if (!class_exists ('Mail_Provider_Abstract'))
{
    include dirname (__FILE__) . '/Abstract.php';
}

class Mail_Provider_Icq extends Mail_Provider_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (

	);

	/**
	 * @desc Мейлер
	 * @var Client_Icq
	 */
	protected $_icq;

	/**
	 * @desc Последняя ошибка
	 * @var string
	 */
	protected $_lastError = '';

	/**
	 * @desc Создает и возвращает мейлер.
	 * @return PHPMailer
	 */
	protected function _icq ()
	{
		if (!$this->_icq)
		{
			$this->_icq = new Client_Icq (array (
				'login'		=> $this->config ()->from_uin,
				'password'	=> $this->config ()->from_password
			));
		}
		return $this->_icq;
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send (Mail_Message $message, $config)
	{
		$to_name = $message->toName ? $message->toName : 0;

		$this->logMessage ($message, self::MAIL_STATE_SENDING);
		$this->_lastError = '';

		$result = $this->sendEx (
			array (
				$to_name	=> $message->address
			),
			$message->subject,
			$message->body,
			$config
		);

		if ($result)
		{
			$this->logMessage (
				$message,
				self::MAIL_STATE_SUCCESS,
				$this->_lastError
			);
		}
		else
		{
			$this->logMessage (
				$message,
				self::MAIL_STATE_FAIL,
				$this->_lastError
			);
		}

		return $result;
	}

	/**
	 * @desc Отправка сообщения на емейл
	 * @param array|string $addresses
	 * @param string $subject Тема сообщения
	 * @param string $body Тело сообщения
	 * @param array $config
	 */
	public function sendEx ($addresses, $subject, $body, $config)
	{
		$icq = $this->_icq ();

		$this_config = $this->config ();

		$base_charset =
			isset ($config ['base_charset']) ?
				$config ['base_charset'] :
				$this_config ['base_charset'];

		$send_charset =
			isset ($config ['send_charset']) ?
				$config ['send_charset'] :
				$this_config ['send_charset'];

		// Необходимо перекодирвоание
		$recoding =
			$base_charset &&
			$send_charset &&
			$base_charset != $send_charset;

		// Тело
		$body =
			$recoding ?
				iconv ($base_charset, $send_charset, $body) :
				$body;

		$result = false;

		try
		{
			$result = $this->_icq->send (
				new Client_Icq_Reciever (
					'Client_Icq_Reciever',
					array (
						'name'	=> '',
						'icq'	=> is_array ($addresses)
							? $addresses [0]
							: $addresses
					)
				),
				$body
			);
		}
		catch (Exception $e)
		{
			$this->_lastError = $e->getMessage ();
			return false;
		}

		return $result;
	}

}
