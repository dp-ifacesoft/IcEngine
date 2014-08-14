<?php

class Data_Provider_JsHttpRequest extends Data_Provider_Abstract
{
	
	public function get ($key, $plain = false)
	{
		return isset ($GLOBALS ['_RESULT'][$key]) ? $GLOBALS ['_RESULT'][$key] : null;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$GLOBALS ['_RESULT'][$key] = $value;
	}
	
}