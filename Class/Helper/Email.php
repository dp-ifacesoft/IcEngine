<?php

/**
 * Помощник для работы с email.
 * Спрашивается, нахуя он нужен
 *
 * @author Юрий Шведов
 * @Service("helperEmail")
 */
class Helper_Email extends Helper_Abstract
{
    /**
     * Получает имя пользователя из адреса ящика.
     *
     * @param string $email Электронный адрес.
     * @return string Часть, предшествующая @.
     */
    public function extractName($email)
    {
        return substr($email, 0, strpos($email, '@'));
    }

    public function isValid($email)
    {
        $dataValidatorManager = $this->getService('dataValidatorManager');
        $validatorEmail = $dataValidatorManager->get('Email');
        return $validatorEmail->validate($email);
    }
}