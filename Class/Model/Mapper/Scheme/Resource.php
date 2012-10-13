<?php

/**
 * @desc Ресурс схемы связей модели
 * @author morph
 */
class Model_Mapper_Scheme_Resource
{
	/**
	 * @desc Полученные в ресурс модели
	 * @var array
	 */
	protected $_items;

	/**
	 * @desc Ссылка
	 * @var Model_Mapper_Scheme_Reference_Abstract
	 */
	protected $_reference;

	public function __construct ($reference)
	{
		$this->_reference = $reference;
	}

	/**
	 * @desc Добавить модель для сохранения
	 * @param array $items
	 */
	public function add ($item)
	{

	}

	/**
	 * @desc Добавить модель
	 * @param array $item
	 */
	public function addItem ($item)
	{
		$this->_items [] = $item;
	}

	/**
	 * @desc Удалить модель
	 * @param array $item
	 */
	public function delete ($item)
	{

	}

	/**
	 * @desc Передать модели
	 * @param array $items
	 */
	public function setItems ($items)
	{
		$this->_items = $items;
		return $this;
	}

	/**
	 * @desc Получить модели
	 * @return array
	 */
	public function items ()
	{
		return $this->_items;
	}

	/**
	 * @desc Получить первую модель
	 * @return Model
	 */
	public function get ()
	{
		return $this->_items [0];
	}
}