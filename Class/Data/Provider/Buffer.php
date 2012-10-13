<?php

if (!class_exists ('Data_Provider_Abstract'))
{
	include __DIR__ . '/Abstract.php';
}
/**
 * 
 * @desc Буфер данных. Используется для хранения данных в пределах 
 * 		текущего процесса.
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Provider_Buffer extends Data_Provider_Abstract
{
	
	/**
	 * @desc Содержимое буфера.
	 * @var array
	 */
	protected $_buffer;
	
	/**
	 * Возвращает объект буфера данных.
	 */
	public function __construct ()
	{
		$this->_buffer = array ();
	}
		
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		return isset ($this->_buffer [$key]) ? $this->_buffer [$key] : null;
	}
	
	/**
	 * @desc Всё содержимое буфера.
	 * @return array
	 */
	public function getAll ()
	{
		return $this->_buffer;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::flush()
	 */
	public function flush ($delay = 0)
	{
		$this->_buffer = array ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$this->_buffer [$key] = $value;
	}
}