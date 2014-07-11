<?php

/**
 * Транспорт api, который отправляет данные по средствам http запроса
 * 
 * @author morph
 */
class Api_Transport_Http extends Api_Transport_Abstract
{
    /**
     * @inheritdoc
     */
    public function send($call, $args)
    {
        if (empty($args['url'])) {
            return null;
        }
        $url = $args['url'];
        unset($args['url']);
        $httpUrl = $url . implode('/', (array) $call) . '/' . 
            ($args ? '?' . http_build_query($args) : '');
        return file_get_contents($httpUrl);
    }
}