<?php
/**
 * 
 * @desc Фильтр для сериализации конфигов
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Config_Serialize
{
	
	/**
	 * @desc Десириализация модели в строку
	 * @param Config_Array $data
	 * @return string
	 */
	public function filter (Config_Array $data)
	{
		return
			get_class ($data) . ':' . json_encode ($data->__toArray ());
	}
	
}