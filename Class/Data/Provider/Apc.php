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
        if (!function_exists('apc_fetch')) {
            return null;
        }

        return apc_fetch($this->prefix . $key);
    }
    
    /**
     * @inheritdoc
     */
    public function set($key, $value, $expiration = 0, $tags = array())
    {
        if (!function_exists('apc_store')) {
            return;
        }

        apc_store($this->prefix . $key, $value);
    }
}