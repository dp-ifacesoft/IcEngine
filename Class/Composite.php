<?php

/**
 * @desc Реализация паттерна "Composite". 
 * Необходим для обращения к коллекции объектов, как к объекту
 * @author Илья Колесников
 * @package IcEngine
 */
class Composite 
{
	/**
	 * @desc Объекты композита
	 * @var mixed
	 */
	protected $_items = array ();
	
	/**
	 * (non-PHPDoc)
	 */
	public function __call ($method, $args)
	{
		foreach ($this->_items as $item)
		{
			$item->$method ($args);
		}
	}
	
	/**
	 * (non-PHPDoc)
	 * @param array<mixed> $items 
	 */
	public function __construct ($items)
	{
		$this->_items = $items;
	}
}