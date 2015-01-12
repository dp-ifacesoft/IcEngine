<?php

/**
 * Абстрактный класс подключения к удалённому Api наших сайтов
 *
 * @author Ziht
 */
abstract class Api_Connector_Abstract
{
    protected $config
        = array(
            'password' => 'wQaWBoK5Y7VcGA2c'
        );

    /**
     * Имя домена
     */
    protected $domain;

    /**
     * Выполнение запроса к апи
     *
     * @param array $params необходимо передать команду(cmd) и параметры(params), если есть
     *
     * @return array
     */
    public function execute($params)
    {
        $config = $this->config;
        $domain = $this->domain;
        $cmd = $params['cmd'];
        $sig = md5("$cmd" . $config['password']);
        $url = "http://$domain/api/?cmd=$cmd&sig=$sig";
        $data = ['params' => urlencode(json_encode($params['params']))];
        $curlChannel = curl_init();
        curl_setopt_array(
            $curlChannel, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $data
            ]
        );
        $jsonData = curl_exec($curlChannel);
        curl_close($curlChannel);
        $jsonData = preg_replace("#^.*?{#", '{', $jsonData);
        $data = json_decode($jsonData, true);
        return $data;
    }
}