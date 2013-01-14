<?php
/**
 * 
 * @desc Фильтр преобразует строку в кодировке utf-8 к кодировке windows-1251,
 * если она начинается с "u://".
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Unicode_Cp1251
{
	
	/**
	 * @desc Обычная фильтрация
	 * @param string $data
	 * @return string
	 */
	public function filter ($data)
	{
		if (!is_string($data) || (strncmp ($data, 'u://', 4) != 0 ))
		{
			return $data;
		}
		
		return iconv ('UTF-8', 'CP1251', substr ($data, 4));
	}
	
}