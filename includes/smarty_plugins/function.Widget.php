<?php
/**
 * @desc Плагин смарти для вызова виджета.
 * @param array $params
 * @return string
 */
function smarty_function_Widget (array $params)
{
	$helper = new View_Helper_Widget ();
	return $helper->get ($params);
}