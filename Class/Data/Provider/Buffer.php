<?php

/**
 * Буфер данных. Используется для хранения данных в пределах текущего процесса
 * 
 * @author goorus, morph
 */
class Data_Provider_Buffer extends Data_Provider_Abstract
{
	/**
	 * Содержимое буфера.
	 * 
     * @var array
	 */
	protected $buffer;
	
	/**
	 * Возвращает объект буфера данных
	 */
	public function __construct()
	{
		$this->buffer = array();
	}
		
	/**
	 * @inheritdoc
	 */
	public function get($key, $plain = false)
	{
		return isset($this->buffer[$key]) ? $this->buffer[$key] : null;
	}
	
	/**
	 * Всё содержимое буфера.
	 * 
     * @return array
	 */
	public function getAll()
	{
		return $this->buffer;
	}
	
	/**
	 * @inheritdoc
	 */
	public function flush($delay = 0)
	{
		$this->buffer = array();
	}
	
	/**
	 * @inheritdoc
	 */
	public function set($key, $value, $expiration = 0, $tags = array())
	{
		$this->buffer[$key] = $value;
	}
}