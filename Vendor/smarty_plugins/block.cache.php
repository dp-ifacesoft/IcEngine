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
    $serviceLocator = IcEngine::serviceLocator();
    $viewCacheFragmentManager = $serviceLocator->getService(
        'viewCacheFragmentManager'
    );
    $fragmentName = $params['key'];
    unset($params['key']);
    $fragment = $viewCacheFragmentManager->get($fragmentName, $params);
    if (!$content && $fragment->isValid()) {
        $repeat = false;
        echo $fragment->content();
        return true;
    }
    $viewCacheFragmentManager->set($fragmentName, $content, $params);
    return $content;
}