<?php
/**
 * 
 * @param array $params
 * @return string
 */
function smarty_function_Helper (array $params)
{
    $helper = $params ['call'];
    Loader::load ('View_Helper_Manager');
    return View_Helper_Manager::get ($helper, $params);
}