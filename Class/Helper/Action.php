<?php

class Helper_Action
{
	
	/**
	 * Строит путь по названию метода.
	 * @param string $method
	 * 		__METHOD__
	 * @param string $sub
	 * 		Дополнителнение к названию файла
	 * @param string $ext
	 * 		Расширение 
	 * @return string
	 */
	public static function path ($method, $sub = '', $ext = '.tpl')
	{
		return 
			str_replace (array ('::', '_'), '/', $method) . 
			$sub .
			$ext;
	}
	
}