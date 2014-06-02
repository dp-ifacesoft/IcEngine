<?php

if (!class_exists ('Data_Provider_Abstract'))
{
	include dirname (__FILE__) . '/Abstract.php';
}

class Data_Provider_View extends Data_Provider_Abstract
{
	
	public function get ($key, $plain = false)
	{
		return null;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		View_Render_Manager::getView ()->assign ($key, $value);
	}
	
}