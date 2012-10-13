<?php

class Data_Provider_Session extends Data_Provider_Abstract
{
	
	public function get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}
		
		$key = $this->prefix . $key;
		
		return isset ($_SESSION [$key]) ? $_SESSION [$key] : null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll ()
	 */
	public function getAll ()
	{
		return $_SESSION;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key);
		}
		
		$key = $this->prefix . $key;
		
		$_SESSION [$key] = $value;
	}
	
}