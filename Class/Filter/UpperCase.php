<?php
/**
 * 
 * Приводит строку к верхнему регистру 
 * @author Юрий
 * @package IcEngine
 * 
 */
class Filter_UpperCase
{
	
	/**
	 * 
	 * @param string $data
	 * @return string
	 */
	public function filter ($data)
	{
		return mb_strtoupper ($data);
	}
	
}