<?php

/**
 * 
 * @param array $params
 * @return string
 */
function smarty_function_Paginator (array $params)
{
	Loader::load ('View_Helper_Paginator');
	$helper = new View_Helper_Paginator ();
	return $helper->get ($params);
}