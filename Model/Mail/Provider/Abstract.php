<?php
/**
 * 
 * @desc Провайдер для отправки сообщений пользователя.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Provider_Abstract extends Model_Factory_Delegate
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
	 * @desc Запись в лог состояния сообщения.
	 * @param Mail_Message $message
	 * @param string $state Состояние отправки
	 * @param mixed $comment [optional] Дополнительная информация.
	 */
	public function logMessage (Mail_Message $message, $state, $comment = null)
	{
		Loader::load ('Mail_Message_Log');
		$log = new Mail_Message_Log (array (
			'time'				=> Helper_Date::toUnix (),
			'Mail_Provider__id'	=> $this->id,
			'Mail_Message__id'	=> $message->id,
			'state'				=> $state,
			'comment'			=> json_encode ($comment)
		));
		$log->save ();
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
		$this->logMessage ($message, self::MAIL_STATE_FAIL);
		return false;
	}
	
}