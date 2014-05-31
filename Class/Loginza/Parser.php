<?php

/**
 * Парсер для соц. данных, полученных с логинзы
 *
 * @author morph
 * @Service("loginzaParser")
 */
class Loginza_Parser
{
    /**
     * Получить парсер логинзы по провайдеру
     *
     * @param string $provider
     * @return Loginza_Parser_Abstract
     */
    public function byProvider($provider)
    {
        static $regexp = '#(?:\://|.*?\.)([^.]+)\.(?:ru|com)#';
        $matches = array();
        preg_match_all($regexp, $provider, $matches);
        $className = 'Loginza_Parser_' . ucfirst($matches[1][0]);
        $parser = new $className;
        return $parser;
    }
}