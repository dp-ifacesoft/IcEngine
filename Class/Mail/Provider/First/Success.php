<?php

/**
 * Провайдер сообщений, который отправляет сообщение по списку
 * провайдеров до первой успешной отправки.
 * 
 * @author goorus, morph
 */
class Mail_Provider_First_Success extends Mail_Provider_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array (
		// Набор провайдеров
		'providers'		=> null//'Sms_Dcnk,Sms_Littlesms,Sms_Yakoon'
	);

	/**
	 * @inheritdoc
	 */
	public function send(Mail_Message $message, $config)
	{
		$this->logMessage($message, self::MAIL_STATE_SENDING);
		$providers = isset($config['providers']) 
            ? $config['providers'] 
            : $this->config()->providers;
		if (!is_array($providers)) {
			$providers = explode(',', $providers);
		}
		$modelManager = $this->getService('modelManager');
		foreach ($providers as $name) {
			/**
			 * Реальный провайдер
			 * 
             * @var Mail_Provider_Abstract $provider
			 */
			$provider = $this->getService('mailProvider')->byName($name);
			if ($provider && $provider->send($message, $config)) {
				$this->logMessage(
					$message,
					self::MAIL_STATE_SUCCESS,
					'provider: ' . $name
				);
				return true;
			}
		}
		$this->logMessage(
			$message,
			self::MAIL_STATE_FAIL,
			'providers: ' . var_export($providers, true)
		);
		return false;
	}
}