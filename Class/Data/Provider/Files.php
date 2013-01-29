<?php
/**
 * 
 * @desc Провайдер для получения файлов из POST запроса.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Provider_Files extends Data_Provider_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		if (isset ($_FILES [$key]))
		{
			return $_FILES [$key];
		}
		return null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll ()
	{
		return $_FILES;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		
	}
	
}