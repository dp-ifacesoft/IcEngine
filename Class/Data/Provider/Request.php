<?php
/**
 * 
 * @desc Продвайдер $_REQUEST данных.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Data_Provider_Request extends Data_Provider_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		return isset ($_REQUEST [$key]) ? $_REQUEST [$key] : null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll ()
	{
		return $_REQUEST;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$_REQUEST [$key] = $value;
	}
	
}