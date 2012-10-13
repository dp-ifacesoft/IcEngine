<?php

class Data_Provider_Json extends Data_Provider_Abstract
{
	public function get ($key, $plain = false)
	{
		return null;	
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		echo json_encode ($value);
	}
}