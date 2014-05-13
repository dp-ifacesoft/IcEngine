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
}