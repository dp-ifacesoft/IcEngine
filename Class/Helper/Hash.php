<?php

/**
 * Помощник для Hash'ей
 *
 * @author Apostle
 * @Service("helperHash")
 */
class Helper_Hash extends Helper_Abstract
{
    /**
     * является ли мд5
     * @param string $md5
     * @return boolean
     */
    function isValidMd5 ($md5 = '')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

}
