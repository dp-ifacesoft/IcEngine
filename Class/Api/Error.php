<?php

/**
 * Ошибки к api
 *
 * @author markov
 * @Service("apiError")
 */
class Api_Error
{
    const COMMAND_NOT_FOUND = 1;
    const ACCESS_DENIED = 2;
    
    /**
     * Получить описание ошибки
     * 
     * @param integer $error код ошибки
     */
    public function getErrorDescription($error)
    {
        static $descriptions = array(
            self::COMMAND_NOT_FOUND => 'Команда не найдена',
            self::ACCESS_DENIED     => 'Доступ запрещен'
        );
        return isset($descriptions[$error]) ? $descriptions[$error] : null;
    }
}
