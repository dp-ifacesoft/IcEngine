<?php
/**
 *
 * @desc Форматирование телефонного номера.
 * @package IcEngine
 * @param string $string
 * @return string
 *
 */
function smarty_modifier_phone_format ($phone, $prefix = '+', $separator = '-')
{
	$mobile = Helper_Phone::parseMobile ($phone);
	if (!$mobile)
	{
		return $phone;
	}

	return
		$prefix . $mobile [0] . $separator .
		substr ($mobile, 1, 3) . $separator .
		substr ($mobile, 4, 3) . $separator .
		substr ($mobile, 7, 2) . $separator .
		substr ($mobile, 9);
}