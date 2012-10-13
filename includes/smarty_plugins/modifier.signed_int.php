<?php
/**
 * 
 * @desc Выводит знак перед числом.
 * @package IcEngine
 * @author Yury Shvedov 
 * @param string $string
 * @return string
 * 
 */
function smarty_modifier_signed_int ($string)
{
	if ($string > 0)
	{
		return '+' . (int) $string;
	}
	
	return (int) $string;			
}