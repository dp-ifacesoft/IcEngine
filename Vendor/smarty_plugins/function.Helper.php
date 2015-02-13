<?php

/**
 * Вызов хелпера представления
 * 
 * @param array $params
 * @return string
 */
function smarty_function_Helper(array $params)
{
    $helper = $params['call'];
	$locator = IcEngine::serviceLocator();
	$viewHelperManager = $locator->getService('viewHelperManager');
    return $viewHelperManager->get($helper, $params);
}