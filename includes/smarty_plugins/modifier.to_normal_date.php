<?php
/**
 *
 * @desc
 * @param string $string
 * @return string
 *
 */
function smarty_modifier_to_normal_date ($string)
{
	return Helper_Date::toCasualDate ($string);
}