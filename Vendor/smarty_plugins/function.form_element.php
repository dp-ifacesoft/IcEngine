<?php

/**
 * Елемент формы
 *
 * @param array $params
 * @return string
 * @tutorial
 * {form_element instance=$form->element('title')}
 */
function smarty_function_form_element($params)
{
    $serviceLocator = IcEngine::serviceLocator();
    $controllerManager = $serviceLocator->getService('controllerManager');
    $html = $controllerManager->html('Form_Element_View/index', array(
        'form'          => $params['form'],
        'elementName'   => $params['name']
    ));
	return $html;
}