<?php
/**
 * 
 * @desc Модификатор для прерывания
 * @package IcEngine
 * 
 */
function smarty_modifier_die ()
{
	echo '<pre>';
	print_r (func_get_args ());
	echo '</pre>';
	die ();
}