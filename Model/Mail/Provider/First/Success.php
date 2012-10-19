<?php

/**
 *
 * @desc Провайдер сообщений, который отправляет сообщение по списку
 * провайдеров до первой успешной отправки.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Provider_First_Success extends Mail_Provider_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		// Набор провайдеров
		'providers'		=> null//'Sms_Dcnk,Sms_Littlesms,Sms_Yakoon'
	);

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send (Mail_Message $message, $config)
	{
		$this->logMessage ($message, self::MAIL_STATE_SENDING);

		$providers =
			isset ($config ['providers']) ?
				$config ['providers'] :
				$this->config ()->providers;

		if (!is_array ($providers))
		{
			$providers = explode (',', $providers);
		}

		foreach ($providers as $provider_name)
		{
			/**
			 * @desc Реальный провайдер
			 * @var Mail_Provider_Abstract $provider
			 */
			$provider = Model_Manager::byQuery (
				'Mail_Provider',
				Query::instance ()
				->where ('name', $provider_name)
			);

			if ($provider && $provider->send ($message, $config))
			{
				$this->logMessage (
					$message,
					self::MAIL_STATE_SUCCESS,
					'provider: ' . $provider_name
				);
				return true;
			}
		}

		$this->logMessage (
			$message,
			self::MAIL_STATE_FAIL,
			'providers: ' . var_export ($providers, true)
		);

		return false;
	}

}