<?php

/**
 * @desc возвращает ссылку
 * @return string
 */
function smarty_function_mobyCityLink ()
{
	return Helper_City::getLink();
}