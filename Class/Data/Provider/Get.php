<?php
/**
 * 
 * @desc Работа с данными, переданными методом $_GET
 * @author Юрий
 *
 */
class Data_Provider_Get extends Data_Provider_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		return isset ($_GET [$key]) ? urldecode ($_GET [$key]) : null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll ()
	{
		return $_GET;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$_GET [$key] = $value;
	}
	
}