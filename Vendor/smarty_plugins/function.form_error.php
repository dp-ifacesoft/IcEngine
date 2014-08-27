<?php

/**
 * Вывод ошибки элемента формы
 *
 * @param array $params
 * @return string
 * @tutorial
 * {form_error form=$form element='title'}
 */
function smarty_function_form_error($params)
{
    $serviceLocator = IcEngine::serviceLocator();
    $controllerManager = $serviceLocator->getService('controllerManager');
    $html = $controllerManager->html('Form_Element_Error/index', array(
        'form'          => $params['form'],
        'elementName'   => $params['element']
    ));
	return $html;
}