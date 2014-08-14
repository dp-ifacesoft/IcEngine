<?php

/**
 * Хелпер для отправки оригинального текста в Яндекс
 *
 * @author nastya
 * @Service("helperYandexOriginalText")
 */
class Helper_Yandex_Original_Text extends Helper_Abstract
{
    /**
     * Токен доступа. Чтобы получить новый токен, необходимо перейти по ссылке
     * https://oauth.yandex.ru/authorize?response_type=token&client_id=acec723f3b504fb4a91a60c75546e14a
     * и подвердить разрешение приложению осуществлять доступ к данным.
     * В ответе получим #access_token
     */
    protected $config = array(
        'accessToken'  => '94f2d09a73e042f280fae50d9df83372'
    );
    /**
     * Отправка оригинального текста
     * @param string $text оригинальный текст
     * @param string $domain адрес сайта, на котором публикуется текст
     * @return array ответ сервера яндекс
     */
    public function sendOriginalText($text, $domain)
    {
        $helperNetwork = $this->getService('helperNetwork');
        $config = $this->config();
        $sitesListUrl = [];
        $matches = [];
        $siteInfoUrl = [];
        $header[] = 'Authorization: OAuth ' . $config['accessToken'];
        $serviceDocUrl = 'https://webmaster.yandex.ru/api/me';
        $serviceDoc = $helperNetwork->getWithHeaders($serviceDocUrl, $header);
        $isFound = preg_match_all('#collection href="(.*)">#Uis', $serviceDoc['content'], $sitesListUrl); 
        $sitesList = $helperNetwork->getWithHeaders($sitesListUrl[1][0], $header);
        $isFound = preg_match_all('#<host.*</host>#Uis', $sitesList['content'], $matches);
        foreach ($matches[0] as $match) {
            if (stripos($match, '<name>' . $domain . '</name>')) {
                $isFound = preg_match_all('#href="(.*)"#Uis', $match, $siteInfoUrl);
            }
        }
        $isFound = preg_match_all('#hosts/([0-9]*)"#Uis', $siteInfoUrl[0][0], $host);
        $textStripped = strip_tags(html_entity_decode($text));
        $end = 0;
        $start = 0;
        $iterations = ceil(strlen($text)/32000);
        $postHeader[] = 'Authorization: OAuth ' . $config['accessToken'];
        for ($i = 0; $i < $iterations; $i++) {
            $end += 32000;
            $textPart = substr($textStripped, $start, $end);
            $start += 32000;
            $originalText = urlencode('<original-text><content>' . $textPart . '</content></original-text>');
            $postHeader[] = 'Content-Length: ' . strlen($textPart);
            $sendOriginal = $helperNetwork->postWithHeaders(
                'https://webmaster.yandex.ru/api/v2/hosts/' . $host[1][0] . '/original-texts/', $header, $originalText);
        }
        return $sendOriginal;
    }
}
