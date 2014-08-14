<?php
/**
 * @desc Плагин смарти для вызова контроллера.
 * @param array $params
 * @return string
 */
function smarty_function_Controller (array $params)
{
	return Controller_Manager::html ($params ['call'], $params);
}