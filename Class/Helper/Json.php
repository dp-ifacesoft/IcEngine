<?php
/**
 * 
 * @desc Помощник для работы с json
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Helper_Json
{
	
	/**
	 * @desc json_encode средствами PHP
	 * @param mixed $data
	 * @return string 
	 */
	public static function nativeEncode ($data)
	{
		if (is_object ($data) && method_exists ($data, '__toArray'))
		{
			$data = $data->__toArray ();
		}
		
		return json_encode ($data);
	}
	
	/**
	 * @desc json_decode ($json, true)
	 * @param string $json
	 * @return mixed
	 */
	public static function nativeDecode ($json)
	{
		return json_decode ($json, true);
	}
	
	/**
	 * @desc JSON кодирование с форматированием
	 * @param mixed $in Данные
	 * @param integer $indent Отступ
	 * @return string
	 */
	public static function readableEncode ($in, $indent = 0)
	{
		$_myself = __FUNCTION__;
		
		/**
		 * @desc Экранирование строк
		 * @param string
		 * @return string
		 */
		$_escape = function ($str)
		{
			return preg_replace ('!([\b\t\n\r\f\'\\"])!', "\\\\\\1", $str);
		};
	
		$out = '';
	
		foreach ($in as $key => $value)
		{
			$out .= str_repeat ("\t", $indent + 1);
			$out .= "\"" . $_escape ((string) $key) . "\": ";

			if (is_object ($value) || is_array ($value))
			{
				$out .= "\n";
				$out .= $_myself ($value, $indent + 1);
			}
			elseif (is_bool ($value))
			{
				$out .= $value ? 'true' : 'false';
			}
			elseif (is_null ($value))
			{
				$out .= 'null';
			}
			elseif (is_string ($value))
			{
				$out .= "\"" . $_escape ($value) . "\"";
			}
			else
			{
				$out .= $value;
			}
	
			$out .= ",\n";
		}
	
		if (!empty ($out))
		{
			$out = substr ($out, 0, -2);
		}
	
		$out = str_repeat ("\t", $indent) . "{\n" . $out;
		$out .= "\n" . str_repeat ("\t", $indent) . "}";
	
		return $out;
	}
	
}
