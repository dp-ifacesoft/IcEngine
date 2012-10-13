<?php
/**
 * 
 * @desc Провайдер для отправки сообщений по почте.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */

if (!class_exists ('Mail_Provider_Abstract'))
{
    include dirname (__FILE__) . '/Abstract.php';
}

class Mail_Provider_Mimemail extends Mail_Provider_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		// С ящика
		'from_email'		=> 'root@icengine.com',
		// От кого
		'from_name'			=> 'IcEngine',
		// Путь до PHPMailer
		'phpmailer_path'	=> 'PHPMailer/class.phpmailer.php',
		// Исходная кодировка
		'base_charset'		=> 'utf-8',
		// Кодировка отправки
		'send_charset'		=> 'utf-8',
		// Использовать SMTP
		'smtp'				=> false,
		// SMTP сервер
		'smtp_host'			=> 'smtp.gmail.com',
		// SMTP порт
		'smtp_port'			=> 465, // 587 for TLS, 25 default smtp
		// SMTP поле from
		// Обычным требованием является, чтобы поле "От" совпадало
		// с логином пользователя.
		'smtp_sender'		=> 'ic1engine@gmail.com',
		// SMTP Логин
		'smtp_username'		=> 'ic1engine@gmail.com',
		// пароль
		'smtp_password'		=> '',
		// SSL
		'smtp_secure'		=> 'ssl'
	);
	
	/**
	 * @desc Мейлер
	 * @var PHPMailer
	 */
	protected $_mailer;
	
	/**
	 * @desc Последняя ошибка
	 * @var string
	 */
	protected $_lastError = '';
	
	/**
	 * @desc Создает и возвращает мейлер.
	 * @return PHPMailer
	 */
	protected function _mailer ()
	{
		if (!$this->_mailer)
		{
			$this->_mailer = new PHPMailer ();
		}
		return $this->_mailer;
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
		Loader::requireOnce (
			$this->config ()->phpmailer_path,
			'includes'
		);
		
		$mail = $this->_mailer ();
		
		$mail->ClearAddresses ();
		$mail->ClearReplyTos ();
		
		foreach ((array) $addresses as $to_name => $address)
		{
			$mail->addAddress (
				$address,
				is_numeric ($to_name) ? '' : $to_name
			);
		}
		
		$this_config = $this->config ();
		
		$mail->From =
			isset ($config ['from_email']) ? 
				$config ['from_email'] : 
				$this_config ['from_email'];
				
		$mail->FromName =
			isset ($config ['from_name']) ? 
				$config ['from_name'] : 
				$this_config ['from_name'];
				
		if (isset ($config ['reply_address']) && $config ['reply_address'])
		{
			$reply_name = 
				isset ($config ['reply_name']) 
				? $config ['reply_name'] 
				: '';
			$mail->AddReplyTo ($config ['reply_address'], $reply_name);
		}
		elseif ($this_config ['reply_address'])
		{
			$reply_name =
				isset ($this_config ['reply_name']) 
				? $this_config ['reply_name']
				: '';
			
			$mail->AddReplyTo ($this_config ['reply_address'], $reply_name);
		}
				
		if ($this_config ['send_charset'])
		{
			$mail->CharSet = $this_config ['send_charset'];
		}
				
		$mail->IsHTML (true);
		
		if ($this_config ['smtp'])
		{
			// Отправка через SMTP
			$mail->IsSMTP ();
			$mail->SMTPAuth = true;
			$mail->SMTPDebug = false;
			$mail->Host = $this_config ['smtp_host'];
			$mail->Port = $this_config ['smtp_port'];
			$mail->Username = $this_config ['smtp_username'];
			$mail->Password = $this_config ['smtp_password'];
			
			if ($this_config ['smtp_sender'])
			{
				$mail->Sender = $this_config ['smtp_sender'];
				$mail->From = $this_config ['smtp_sender'];
			}
			
			if ($this_config ['smtp_secure'])
			{
				$mail->SMTPSecure = $this_config ['smtp_secure'];
			}
		}
		
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
				
		// Тема
		$mail->Subject = 
			$recoding ?
				iconv ($base_charset, $send_charset, $subject) : 
				$subject;
				
		// Тело
		$mail->Body =
			$recoding ? 
				iconv ($base_charset, $send_charset, $body) :
				$body;

		try
		{
			$result = $mail->Send ();
		}
		catch (Exception $e)
		{
			$this->_lastError = $e->getMessage ();
			return false;
		}
		
		$this->_lastError = $mail->ErrorInfo;
				
		return $result;
	}
	
}
