<?php

/**
 * Провайдер для отправки сообщений пользователя.
 *
 * @author goorus, neon
 */
class Mail_Provider_Abstract
{

	/**
	 * @desc Состояние отправки
	 * @var string
	 */
	const MAIL_STATE_SENDING	= 'sending';

	/**
	 * @desc Отправка прервана
	 * @var string
	 */
	const MAIL_STATE_FAIL		= 'fail';

	/**
	 * @desc Отправка успешно завершена
	 * @var stringы
	 */
	const MAIL_STATE_SUCCESS	= 'success';

    /**
     * Получить экземпляр по имени
     *
     * @param string $name
     * @return Mail_Provider_Abstract
     */
    public function byName($name)
	{
		$modelManager = $this->getService('modelManager');
        return $modelManager->byOptions(
            'Mail_Provider',
            array(
                'name'  => '::Name',
                'value' => $name
            )
        );
	}
    
    /**
	 * Загружает и возвращает конфиг для провайдера
     *
	 * @return Objective
	 */
	public function config()
	{
		if (!is_object($this->config)) {
			$configManager = $this->getService('configManager');
            $this->config = $configManager->get(
				get_class($this), $this->config
			);
		}
		return $this->config;
	}
    
    /**
     * Получить имя провайдера
     * 
     * @return string
     */
    public function getName()
    {
        return substr(get_class($this), strlen('Mail_Provider_'));
    }
    
    /**
     * Получить сервис по имени
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }

	/**
	 * Запись в лог состояния сообщения.
     *
	 * @param Mail_Message $message
	 * @param string $state Состояние отправки
	 * @param mixed $comment [optional] Дополнительная информация.
	 */
	public function logMessage(Mail_Message $message, $state, $comment = null)
	{
        $helperDate = $this->getService('helperDate');
		$log = new Mail_Message_Log(array(
			'time'				=> $helperDate->toUnix(),
			'mailProvider'      => $this->getName(),
			'Mail_Message__id'	=> $message->id,
			'state'				=> $state,
			'comment'			=> json_encode($comment)
		));
		$log->save();
	}

    /**
	 * @desc Отправка сообщений.
	 * @param Mail_Message $message Сообщение.
	 * @param array $config Параметры.
	 * @return integer|false Идентикатор сообщения в системе провайдера
	 * или false.
	 */
	public function send (Mail_Message $message, $config)
	{
		$this->logMessage($message, self::MAIL_STATE_FAIL);
		return false;
	}

}