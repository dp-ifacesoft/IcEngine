<?php
/**
 * 
 * @desc Продвайдер POST данных.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Data_Provider_Post extends Data_Provider_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		return isset ($_POST [$key]) ? $_POST [$key] : null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll ()
	{
		return $_POST;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$_POST [$key] = $value;
	}
	
}