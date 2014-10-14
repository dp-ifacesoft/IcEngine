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
     * Нормализовать путь
     * 
     * @param string $path путь
     */
    public function normalize($path)
    {
        if (!$path) {
            return;
        }
        return '/' . trim($path, '/') . '/';
    }
    
    /**
     * Проверить, есть ли url в списке роутов
     * 
     * @param string $url
     * @param array $routes список роутов
     * @return boolean 
     */
    public function byRoute($url, $routes)
    {
        if (in_array($url, $routes)) {
            return true;
        }
        foreach ($routes as $route)
        if (preg_match('#' . $route . '#', $url)) {
            return true;
        }
        return false;
    }
}
