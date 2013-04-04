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
    $dataProviderManager = $serviceLocator->getService('dataProviderManager');
    $configManager = $serviceLocator->getService('configManager');
    $blockConfig = $configManager->get('Block');
    $expiration = 0;
    $notCache = false;
    if ($blockConfig && $blockConfig[$blockName]) {
        $blockConfig = $blockConfig[$blockName];
        $expiration = $blockConfig['expiration'];
        $notCacheConfig = $blockConfig['notCache'];
        if ($notCacheConfig) {
            foreach ($notCacheConfig as $param => $value) {
                if (isset($params[$param]) && $params[$param] === $value) {
                    $notCache = true;
                    break;
                }
            }
        }
    }
    if (!$notCache) {
        $user = $serviceLocator->getService('user')->getCurrent();
        if ($user->key()) {
            $notCache = $user->hasRole('editor');
        }
    }
    $time = time();
    $provider = $dataProviderManager->get('Block');
    if (!$content && !$notCache && $expiration) {
        $cache = $provider->get($key);
        if ($cache) {
            if ($cache['e'] + $expiration > $time) {
                $repeat = false;
                echo $cache['v'];
                return true;
            }
        }
    }
    if (!$notCache && $expiration) {
        $cache = array(
            'e' => $time,
            'v' => $content
        );
        $provider->set($key, $cache);
    }
    return $content;
}