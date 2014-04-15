<?php

/**
 * Блок для формы
 *
 * @param array $params Параметры.
 * @param string $content Код шаблона.
 * @param Smarty $smarty Экземпляр смарти.
 * @param boolean $repeat
 *
 * @tutorial
 * {form instance=$form}{/form}
 */
function smarty_block_form($params, $content, $smarty, &$repeat)
{
    $serviceLocator = IcEngine::serviceLocator();
    $controllerManager = $serviceLocator->getService('controllerManager');
    $html = $controllerManager->html('Form_View/index', array(
        'form'      => $params['instance'],
        'content'   => $content
    ));
	return $html;
}