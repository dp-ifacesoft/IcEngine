<?php
/**
 * 
 * @desc Фильтр получения даты
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Date
{
	
	/**
	 * Получение даты в UNIX формате YYYY-MM-DD hh:mm:ss. 
	 * @param string $data
	 * @return string
	 */
	public function filter ($data)
	{
		return Helper_Date::toUnix (Helper_Date::strToTimestamp ($data));
	}
	
	/**
	 * 
	 * @param string $field
	 * @param stdClass $data
	 * @param stdClass|Objective $scheme
	 * @return mixed
	 */
	public function filterEx ($field, $data, $scheme)
	{
		$timestamp = 
			(isset ($scheme->input) && $scheme->input == 'php') ?
			strtotime ($data->$field) :
			Helper_Date::strToTimestamp ($data->$field);
			
		if (isset ($scheme->output) && $scheme->output == 'timestamp')
		{
			return $timestamp;
		}
		
		return Helper_Date::toUnix ($timestamp);
	}
	
}