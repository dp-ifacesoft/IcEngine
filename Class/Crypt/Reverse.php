<?php

/**
 * Описание Reverse
 *
 * @author Apostle
 */
class Crypt_Reverse extends Crypt_Abstract
{
    /**
     * @inheritdoc
     */
    public function encode($input, $key = null)
    {
        return strrev($input);
    }
    
    /**
     * @inheritdoc
     */
    public function decode($input, $key = null)
    {
        return strrev($input);
    }
}
