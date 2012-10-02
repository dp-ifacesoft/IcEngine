<?php

if (!class_exists ('Config_Array'))
{
	include dirname (__FILE__) . '/Array.php';
}

class Config_Php extends Config_Array
{
	
	/**
	 * 
	 * @param string $path
	 * 		Путь до файла конфига.
	 * 		В файле должен быть определен массив $config.
	 */
	public function __construct ($path)
	{
		$config = null;
		
		if (is_file ($path))
		{
			include $path;
		}
		if (isset ($config))
		{
			parent::__construct ($config);
		}
	}
	
}