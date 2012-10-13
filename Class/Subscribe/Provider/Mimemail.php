<?php

class Subscribe_Provider_Mimemail
{
	/**
	 * 
	 * @var string
	 */
	const MIME_MAIL_PATH 	 = '';
	
	/**
	 * 
	 * @param array $mails
	 * @param string $message
	 * @param array $config
	 */
	public function send ($mails, $message, $config)
	{
		Loader::requireOnce (self::MIME_MAIL_PATH);
		if (class_exists ('PHPMailer'))
		{
			$mail = new PHPMailer ();
			foreach ((array) $mails as $email)
			{
				$mail->addAddresses ($email, '');
			}
			$mail->From =
				!empty ($config ['From']['email'])
					? $config ['From']['email']
					: '';
			$mail->FromName =
				!empty ($config ['From']['name'])
					? $config ['From']['name']
					: '';
			$mail->IsHTML (true);
			$mail->Subject = 
				!empty ($config ['Subject'])
					? $config ['Subject']
					: '';
			$mail->Body = $message;
			return $mail->send ();
		}
	}
}