<?php

/**
 * Хелпер для работы с сетью
 * 
 * @author morph
 * @Service("helperNetwork")
 */
class Helper_Network
{
    /**
     * Директория для куков по умолчанию
     */
    const DEFAULT_COOKIE_DIR = '/tmp';
    
    /**
     * User Agent по умолчанию
     */
    const DEFAULT_USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)';
    
    /**
     * Выполнить get-запрос через curl
     * 
     * @param string $url
     * @return array
     */
    public function get($url, $userAgent = null, $cookieDir = null)
    {
        $userAgent = $userAgent ?: self::DEFAULT_USER_AGENT;
        $cookieDir = $cookieDir ?: self::DEFAULT_COOKIE_DIR;
        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handler, CURLOPT_HEADER, 0);
        curl_setopt($handler, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($handler, CURLOPT_ENCODING, '');
        curl_setopt($handler, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($handler, CURLOPT_TIMEOUT, 120);
        curl_setopt($handler, CURLOPT_FAILONERROR, 1);
        curl_setopt($handler, CURLOPT_AUTOREFERER, 1);
        curl_setopt($handler, CURLOPT_COOKIEJAR, $cookieDir);
        curl_setopt($handler, CURLOPT_COOKIEFILE, $cookieDir);
        $content = curl_exec($handler);
        $error = curl_errno($handler);
        $errorMessage = curl_error($handler);
        $headers = curl_getinfo($handler);
        curl_close($handler);
        return array(
            'content'   => $content,
            'error'     => $error,
            'message'   => $errorMessage,
            'headers'   => $headers
        );
    }
    
    /**
     * Выполнить post-запрос через curl
     * 
     * @param string $url
     * @return array
     */
    public function post($url, $params, $userAgent = null, $cookieDir = null)
    {
        $userAgent = $userAgent ?: self::DEFAULT_USER_AGENT;
        $cookieDir = $cookieDir ?: self::DEFAULT_COOKIE_DIR;
        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($handler, CURLOPT_POST, 1);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handler, CURLOPT_HEADER, 0);
        curl_setopt($handler, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($handler, CURLOPT_ENCODING, '');
        curl_setopt($handler, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($handler, CURLOPT_TIMEOUT, 120);
        curl_setopt($handler, CURLOPT_FAILONERROR, 1);
        curl_setopt($handler, CURLOPT_AUTOREFERER, 1);
        curl_setopt($handler, CURLOPT_COOKIEJAR, $cookieDir);
        curl_setopt($handler, CURLOPT_COOKIEFILE, $cookieDir);
        $content = curl_exec($handler);
        $error = curl_errno($handler);
        $errorMessage = curl_error($handler);
        $headers = curl_getinfo($handler);
        curl_close($handler);
        return array(
            'content'   => $content,
            'error'     => $error,
            'message'   => $errorMessage,
            'headers'   => $headers
        );
    }
}