<?php

if (!class_exists ('Data_Provider_Abstract'))
{
	include dirname (__FILE__) . '/Abstract.php';
}

/**
 * 
 * @desc Провайдер данных из адресной строки
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Provider_Router extends Data_Provider_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		return Request::param ($key);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll ()
	{
		return Request::$_params;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		Request::param ($key, $value);
	}
	
}