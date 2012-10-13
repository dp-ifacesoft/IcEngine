<?php

/**
 * @desc возвращает ссылку
 * @return string 
 */
function smarty_function_mobyCityLink ()
{
	Loader::load ('Helper_City');
	return Helper_City::getLink();
}