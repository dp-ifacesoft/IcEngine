<?php

class Filter_Trim
{
	
	public function filter ($data)
	{
		return trim ($data);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $field
	 * @param Objective $data
	 * @param Objective $scheme
	 */
	public function filterEx ($field, $data, $scheme)
	{
		$chars = 
			isset ($scheme->field ['trimChars']) ? 
			$scheme->field ['trimChars'] : null;
		return trim ($data->$field, $chars);
	}
	
}