<?php

/**
 * Елемент формы
 *
 * @param array $params
 * @return string
 * @tutorial
 * {form_element form=$form element='title'}
 */
function smarty_function_form_element($params)
{
    $serviceLocator = IcEngine::serviceLocator();
    $controllerManager = $serviceLocator->getService('controllerManager');
    $attributes = $params;
    unset($attributes['form']);
    unset($attributes['element']);
    $html = $controllerManager->html('Form_Element_View/index', array(
        'form'          => $params['form'],
        'elementName'   => $params['element'],
        'attributes'    => $attributes
    ));
	return $html;
}