<?php

/**
 * Сообщение
 *
 * @author neon, goorus
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
        $helperDate = $this->getService('helperDate');
        $message = new self(array(
            'id' => null,
            'Mail_Template__id' => $template->key(),
            'address' => $dto->address,
            'toName' => $dto->toName,
            'sended' => 0,
            'sendTime' => '',
            'sendDay' => 0,
            'sendTries' => 0,
            'subject' => $template->subject($dto->data),
            'time' => $helperDate->toUnix(),
            'body' => $template->body($dto->data),
            'toUserId' => $dto->toUserId,
            'mailProvider' => $dto->mailProvider,
            'params' => json_encode($mailProviderParams)
        ));
        return $message;
    }

    /**
     * Попытка отправки сообщения
     * @throws ErrorException
     * @return boolean
     */
    public function send()
    {
        $helperDate = $this->getService('helperDate');
        $this->update(array(
            'sendDay' => $helperDate->eraDayNum(),
            'sendTime' => $helperDate->toUnix(),
            'sendTries' => $this->sendTries + 1
        ));
        $provider = $this->mailProvider
            ? $this->getService('mailProvider')->byName($this->mailProvider)
            : null;
        if (!$provider) {
            $provider = new Mail_Provider_Mimemail();
        }
        $result = $provider->send(
            $this, (array)json_decode($this->params, true)
        );
        if ($result === FALSE) {
            return false;
        }

        $this->update(array('sended' => 1));
        return true;
    }
}