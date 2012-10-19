<?php
/**
 *
 * @desc Сообщение
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Message extends Model
{

	/**
	 * @desc Создает копию сообщения.
	 * Содержание сообщения останется неизменным.
	 * Новое сообщение не будет сохранено.
	 * @param string $address [optional] Адрес получателя.
	 * @param string $to_name [optional] Имя получателя.
	 * @return Mail_Message Созданное сообщение.
	 */
	public function cloneTo ($address = null, $to_name = null)
	{
		$fields = $this->_fields;

		if (array_key_exists ('id', $fields))
		{
			unset ($fields ['id']);
		}

		if ($address !== null)
		{
			$fields ['address'] = $address;
		}

		if ($to_name !== null)
		{
			$fields ['toName'] = $to_name;
		}

		return new self ($fields);
	}

	/**
	 * @desc Создает новое сообщение.
	 * @param string $template_name Имя шаблона.
	 * @param string $address Адрес получателя.
	 * @param string $to_name Имя получателя.
	 * @param array $data Данные для шаблона.
	 * @param integer $to_user_id Если получатель - пользователь.
	 * @param string|integer|Mail_Provider $mail_provider Провайдер сообщений.
	 * @param array|Objective $mail_provider_params Параметры для провайдера.
	 * @return Mail_Message Созданное сообщение.
	 */
	public static function create ($template_name, $address, $to_name,
		array $data = array (), $to_user_id = 0, $mail_provider = 1,
		$mail_provider_params = array ())
	{
		$template = Mail_Template::byName ($template_name);

		$mail_provider_params = is_object ($mail_provider_params) ?
			$mail_provider_params->__toArray () :
			$mail_provider_params;

		if (!is_numeric ($mail_provider))
		{
			if (!is_object ($mail_provider))
			{
				$mail_provider = Mail_Provider::byName (
					$mail_provider
				);
			}
			$mail_provider = $mail_provider->id;
		}

		$message = new self (array (
			'Mail_Template__id'		=> $template->id,
			'address'				=> $address,
			'toName'				=> $to_name,
		    'sendTries'				=> 0,
			'subject'				=> $template->subject ($data),
		    'time'					=> date ('Y-m-d H:i:s'),
			'body'					=> $template->body ($data),
			'toUserId'				=> (int) $to_user_id,
			'Mail_Provider__id'		=> $mail_provider,
			'params'				=> json_encode ($mail_provider_params)
		));

		return $message;
	}

	/**
	 * @desc Попытка отправки сообщения
	 * @return boolean
	 */
	public function send ()
	{
		$this->update (array (
			'sendDay'		=> Helper_Date::eraDayNum (),
			'sendTime'		=> date ('Y-m-d H:i:s'),
			'sendTries'	    => $this->sendTries + 1
		));

		$provider = $this->Mail_Provider__id ?
			$this->Mail_Provider :
			null;

		if (!$provider)
		{
			$provider = new Mail_Provider_Mimemail ();
		}

		try
		{
			$result = $provider->send (
				$this,
				(array) json_decode ($this->params, true)
    		);

    		if ($result)
    		{
    			$this->update (array (
    				'sended'	=> 1
    			));
    		}

    		return $result;
		}
		catch (Exception $e)
		{
		    Debug::logVar ($e, 'Sendmail error message');
		    return false;
		}
	}

}