<?php
/**
 * 
 * @desc Фильтр для кодирования Json
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Json_Encode
{
	
	/**
	 * @desc Кодирование Json
	 * @param mixed $data
	 * @return string
	 */
	public function filter ($data)
	{
		return json_encode ($data);
	}
	
}