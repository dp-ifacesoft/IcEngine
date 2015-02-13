<?php

/**
 * Провайдер для отправки сообщений по почте
 * 
 * @author goorus
 */
class Mail_Provider_Mimemail extends Mail_Provider_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array (
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
	 * Мейлер
	 * 
     * @var PHPMailer
	 */
	protected $mailer;

	/**
	 * Создает и возвращает мейлер.
	 * 
     * @return PHPMailer
	 */
	protected function mailer()
	{
		if (!$this->mailer) {
			$this->mailer = new PHPMailer();
		}
		return $this->mailer;
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send(Mail_Message $message, $config)
	{
		$toName = $message->toName ? $message->toName : 0;
		$this->logMessage ($message, self::MAIL_STATE_SENDING);
        $addresses = array();
        if (is_array($message->address)) {
            $addresses = $message->addresses;
        } elseif (strpos($message->address, ',')) {
            $clearSpace = str_replace(' ', '', $message->address);
            $addresses = explode(',', $clearSpace);
        } else {
            $addresses = (array) $message->address;
        }
		$result = $this->sendEx($addresses, $toName, $message->subject,
			$message->body, $config);
		if ($result) {
			$this->logMessage($message, self::MAIL_STATE_SUCCESS);
		} else {
			$this->logMessage($message, self::MAIL_STATE_FAIL);
		}
		return $result;
	}

	/**
	 * Отправка сообщения на емейл
	 * 
     * @param array|string $addresses
	 * @param string $subject Тема сообщения
	 * @param string $body Тело сообщения
	 * @param array $config
	 */
	public function sendEx($addresses, $toName, $subject, $body, $config)
	{
        $loader = IcEngine::getLoader();
		$loader->requireOnce($this->config()->phpmailer_path, 'Vendor');
		$mail = $this->mailer();
		$mail->ClearAddresses();
		$mail->ClearReplyTos();
		foreach ($addresses as $address) {
			$mail->addAddress($address, is_numeric($toName) ? '' : $toName);
		}
		$thisConfig = $this->config();
		$mail->From = isset($config['from_email']) 
            ? $config['from_email'] : $thisConfig['from_email'];
		$mail->FromName = isset($config['from_name']) 
            ? $config['from_name'] : $thisConfig['from_name'];
		if (isset ($config['reply_address']) && $config['reply_address']) {
			$replyName = isset($config['reply_name'])
				? $config['reply_name'] : '';
			$mail->AddReplyTo($config['reply_address'], $replyName);
		} elseif ($thisConfig['reply_address']) {
			$replyName = isset($thisConfig['reply_name'])
				? $thisConfig['reply_name'] : '';
			$mail->AddReplyTo($thisConfig['reply_address'], $replyName);
		}
		if ($thisConfig ['send_charset']) {
			$mail->CharSet = $thisConfig['send_charset'];
		}
		$mail->IsHTML(true);
		if ($thisConfig['smtp']){
			// Отправка через SMTP
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			$mail->SMTPDebug = false;
			$mail->Host = $thisConfig['smtp_host'];
			$mail->Port = $thisConfig['smtp_port'];
			$mail->Username = $thisConfig['smtp_username'];
			$mail->Password = $thisConfig['smtp_password'];
			if ($thisConfig['smtp_sender']) {
				$mail->Sender = $thisConfig['smtp_sender'];
				$mail->From = $thisConfig['smtp_sender'];
			}
			if ($thisConfig['smtp_secure']) {
				$mail->SMTPSecure = $thisConfig['smtp_secure'];
			}
		}
		$baseCharset = isset($config['base_charset']) 
            ? $config['base_charset'] : $thisConfig['base_charset'];
		$sendCharset = isset($config['send_charset']) 
            ? $config['send_charset'] : $thisConfig ['send_charset'];
		// Необходимо перекодирвоание
		$recoding = $baseCharset && $sendCharset && $baseCharset != $sendCharset;
		// Тема
		$mail->Subject = $recoding 
            ? iconv($baseCharset, $sendCharset, $subject) : $subject;
		// Тело
		$mail->Body = $recoding 
            ? iconv($baseCharset, $sendCharset, $body) : $body;
		try {
			$result = $mail->Send();
		} catch (Exception $e) {
			return false;
		}
		return $result;
	}
}