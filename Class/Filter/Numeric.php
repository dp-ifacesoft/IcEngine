<?php

class Filter_Numeric
{
	
	public function filter ($data)
	{
		return (int) $data;
	}
	
	public function filterEx ($field, $data)
	{
		return (int) $data->$field;
	}
	
}