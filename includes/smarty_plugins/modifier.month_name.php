<?php
/**
 * 
 * @author Юрий
 * @package IcEngine
 * 
 * @param integer|string $index Номер месяца от 1 до 12 или дата
 * в формате "d m Y".
 * @param integer $form Форма
 * @return string
 */
function smarty_modifier_month_name ($index, $form = 1, $separator = ' ')
{
	if (is_string ($index) && $separator)
	{
		$p1 = strpos ($index, $separator);
		if ($p1)
		{
			++$p1;
			$p2 = strpos ($index, $separator, $p1);
			if ($p2)
			{
				$l = $p2 - $p1;
				return substr_replace (
					$index, 
					Helper_Date::monthName (substr ($index, $p1, $l), $form),
					$p1,
					$l
				);
			}
		}
	}
	return Helper_Date::monthName ($index, $form);
}