<?php

/**
 * Сообщение
 * @author neon, Юрий Шведов
 *
 * @Service("mailMessage")
 */
class Mail_Message extends Model
{
	/**
	 * Создает копию сообщения.
	 * Содержание сообщения останется неизменным.
	 * Новое сообщение не будет сохранено.
	 * @param string $address [optional] Адрес получателя.
	 * @param string $toName [optional] Имя получателя.
	 * @return Mail_Message Созданное сообщение.
	 */
	public function cloneTo($address = null, $toName = null)
	{
		$fields = $this->fields;
		if (array_key_exists('id', $fields)) {
			unset($fields['id']);
		}
		if ($address !== null) {
			$fields['address'] = $address;
		}
		if ($toName !== null) {
			$fields['toName'] = $toName;
		}
		return new self($fields);
	}

	/**
	 * Создает новое сообщение.
     *
     * @param Dto $dto
	 * @return Mail_Message Созданное сообщение.
	 */
	public function create($dto)
	{
		$mailTemplate = $this->getService('mailTemplate');
		$template = $mailTemplate->byName($dto->template);
		$mailProviderParams = is_object($dto->mailProviderParams) ?
            $dto->mailProviderParams->__toArray() : $dto->mailProviderParams;
        $mailProviderId = $dto->mailProviderId;
		if (!is_numeric($mailProviderId)) {
			if (!is_object($mailProviderId)) {
				$mailProviderService = $this->getService('mailProvider');
				$mailProvider = $mailProviderService->byName($mailProviderId);
			}
			$mailProviderId = $mailProvider->key();
		}
        $helperDate = $this->getService('helperDate');
		$message = new self(array(
			'Mail_Template__id'		=> $template->key(),
			'address'				=> $dto->address,
			'toName'				=> $dto->toName,
		    'sendTries'				=> 0,
			'subject'				=> $template->subject($dto->data),
		    'time'					=> $helperDate->toUnix(),
			'body'					=> $template->body($dto->data),
			'toUserId'				=> $dto->toUserId,
			'Mail_Provider__id'		=> $mailProviderId,
			'params'				=> json_encode($mailProviderParams)
		));
		return $message;
	}

	/**
	 * Попытка отправки сообщения
	 * @return boolean
	 */
	public function send()
	{
        $helperDate = $this->getService('helperDate');
		$this->update(array(
			'sendDay'		=> $helperDate->eraDayNum(),
			'sendTime'		=> $helperDate->toUnix(),
			'sendTries'	    => $this->sendTries + 1
		));
		$provider = $this->Mail_Provider__id ? $this->Mail_Provider : null;
		if (!$provider) {
			$provider = new Mail_Provider_Mimemail();
		}
		try {
			$result = $provider->send(
				$this,
				(array) json_decode($this->params, true)
    		);
            $this->update(array(
                'sended'	=> 1
            ));
    		return $result;
		} catch (Exception $e) {
            $debug = $this->getService('debug');
		    $debug->logVar($e, 'Sendmail error message');
		    return false;
		}
	}
}