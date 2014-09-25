<?php

/**
 * Помощник для URL
 *
 * @author Apostle
 * @Service("helperUrl")
 */
class Helper_Url extends Helper_Abstract
{
    /**
     * нормализовать путь
     */
    public function normalize($path)
    {
        if (!$path) {
            return;
        }
        return '/' . trim($path, '/') . '/';
    }
}
