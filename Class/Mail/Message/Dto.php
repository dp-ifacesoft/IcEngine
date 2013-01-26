<?php

/**
 * Dto для Mail_Message
 *
 * @author neon
 */
class Mail_Message_Dto extends Dto
{
    protected static $defaults = array(
        // имя шаблона
        'template'          => '',
        // адрес получателя email | phone | etc
        'address'               => '',
        // имя получателя
        'toName'                => '',
        // данные для шаблона
        'data'                  => array(),
        // ид пользователя, который получит сообщение
        'toUserId'              => 0,
        // ид провайдера отправки
        'mailProviderId'        => 1,
        // параметры для провайдера отправки
        'mailProviderParams'    => array()
    );
}