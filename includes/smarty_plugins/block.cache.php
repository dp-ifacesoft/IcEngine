<?php
/**
 * Блок для кэширования
 * 
 * @param array $params Параметры.
 * @param string $content Код шаблона.
 * @param Smarty $smarty Экземпляр смарти.
 * @param boolean $repeat
 * 
 * @tutorial
 * {cache key="someKey" param1="value1"}{/cache}
 */
function smarty_block_cache($params, $content, $smarty, &$repeat)
{
    $key = md5(json_encode($params));
    $blockName = $params['key'];
    $serviceLocator = IcEngine::serviceLocator();
    $dataProviderManager = $serviceLocator->get('dataProviderManager');
    $configManager = $serviceLocator->get('configManager');
    $blockConfig = $configManager->get('Block');
    $expiration = 0;
    $notCache = false;
    if ($blockConfig && $blockConfig[$blockName]) {
        $blockConfig = $blockConfig[$blockName];
        $expiration = $blockConfig['expiration'];
        $notCache = $blockConfig['notCache'];
        if ($notCache) {
            foreach ($notCache as $param => $value) {
                if (isset($params[$param]) && $params[$param] == $value) {
                    $notCache = true;
                    break;
                }
            }
        }
    }
    if (!$notCache && $expiration) {
        $time = time();
        $provider = $dataProviderManager->get('Block');
        $cache = $provider->get($key);
        if ($cache) {
            if ($cache['e'] + $expiration > $time) {
                return $cache['v'];
            }
        }
    }
    $content = $smarty->display('string:' . $content);
    $cache = array(
        'e' => $time,
        'v' => $content
    );
    $provider->set($key, $cache);
    return $content;
}