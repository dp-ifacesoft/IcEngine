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
        /**$regExp = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9-_])*@([a-zA-Z0-9-])+(\.[a-zA-Z0-9-]+)*\.([a-zA-Z]{2,6})$/";
        $result = preg_match($regExp, $email);**/
        return strpos($email, '@') !== false;
    }
}