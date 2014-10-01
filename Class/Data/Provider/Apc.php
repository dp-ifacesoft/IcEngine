<?php

/**
 * Провайдер для работы с apc
 * 
 * @author morph
 */
class Data_Provider_Apc extends Data_Provider_Abstract
{
    /**
     * @inheritdoc
     */
    public function get($key, $plain = false)
    {
        return apc_fetch($this->prefix . $key);
    }
    
    /**
     * @inheritdoc
     */
    public function set($key, $value, $expiration = 0, $tags = array())
    {
        apc_store($this->prefix . $key, $value);
    }
}