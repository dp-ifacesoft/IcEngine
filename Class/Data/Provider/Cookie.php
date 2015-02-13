<?php

/**
 * Провайдер для работы с cookie
 * 
 * @author goorus, morph
 */
class Data_Provider_Cookie extends Data_Provider_Buffer
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->buffer = &$_COOKIE;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $expiration = 0, $tags = array())
    {
        if ($expiration) {
            setcookie($key, $value, time() + $expiration);
        } else {
            setcookie($key, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key, $plain = false)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }
}