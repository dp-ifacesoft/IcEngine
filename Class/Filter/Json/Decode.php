<?php
/**
 * 
 * @desc Фильтр для декодирования Json
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Json_Decode
{
	
	/**
	 * @desc Декодирование Json
	 * @param string $data
	 * @return mixed
	 */
	public function filter ($data)
	{
		return json_decode ($data, true);
	}
	
}