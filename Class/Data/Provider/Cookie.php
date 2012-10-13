<?php

class Data_Provider_Cookie extends Data_Provider_Abstract
{
	
	public function get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}
		
		$key = $this->prefix . $key;
		
		return isset ($_COOKIE [$key]) ? $_COOKIE[$key] : null;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key);
		}
		
		$key = $this->prefix . $key;
		
		$_COOKIE [$key] = $value;
	}
	
}