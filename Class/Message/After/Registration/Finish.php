<?php
/**
 * @desc Событие после завершения регистрации, после подтверждения e-maila.
 * @author Юрий
 * @package IcEngine
 */
class Message_After_Registration_Finish extends Message_Abstract
{
	
	/**
	 * @desc Добавление в очередь сообщений
	 * @param Registration $registration
	 * @param array $params
	 */
	public static function push (Registration $registration, 
		array $params = array ())
	{
		IcEngine::$messageQueue->push (
			'After_Registration_Finish',
			array_merge (
				$params,
				array ('registration' => $registration)
			)
		);
	}
	
}