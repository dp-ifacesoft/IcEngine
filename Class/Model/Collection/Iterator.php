<?php

/**
 * Итератор коллекции
 *
 * @author morph
 */
class Model_Collection_Iterator implements Iterator
{
	/**
	 * Коллекция для итерации
	 *
	 * @var Model_Collection
	 */
	protected $collection;

	/**
	 * Текущая модель итерации
	 *
	 * @var Model
	 */
	protected $current;

	/**
	 * Данные для итерации
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Индекс итерации
	 *
	 * @var integer
	 */
	protected $index;

	/**
	 * Является ли модель фабрикой
	 *
	 * @var boolean
	 */
	protected $isFactory;

	/**
	 * Имя ПК модели
	 *
	 * @var string
	 */
	protected $keyField;

	/**
	 * Имя модели
	 *
	 * @var string
	 */
	protected $modelName;

	/**
	 * Конструктор
	 *
	 * @param Model_Collection $collection
	 * @param boolean $isFactory
	 */
	public function __construct($collection, $isFactory)
	{
		$this->collection = $collection;
		$this->isFactory = $isFactory;
		$this->index = 0;
		$this->modelName = $collection->modelName();
		$this->keyField = $collection->keyField();
	}

	/**
	 * @inheritdoc
	 */
	public function current()
	{
		$index = $this->index;
		$fields = $this->data[$index];
		if (!$this->isFactory) {
			$this->current = new $this->modelName($fields);
			return $this->current;
		}
		$key = $this->modelName . '__' . $fields[$this->keyField];
		$this->current = Resource_Manager::get('Model', $key);
		if (!$this->current) {
			$this->current = Model_Manager::create($this->modelName, $fields);
		}
		return $this->current;
	}

	/**
	 * Получить коллекцию для итерации
	 *
	 * @return Model_Collection
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * Получить текущую модель итерации
	 *
	 * @return Model
	 */
	public function getCurrent()
	{
		return $this->current;
	}

	/**
	 * Получить данные для итерации
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Получить индекс итерации
	 *
	 * @return integer
	 */
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * @inheritdoc
	 */
	public function key()
	{
		return $this->index;
	}

	/**
	 * @inheritdoc
	 */
	public function next()
	{
		++$this->index;
	}

	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		$this->index = 0;
	}

	/**
	 * Изменить коллекцию итерации
	 *
	 * @param Model_Collection $collection
	 */
	public function setCollection($collection)
	{
		$this->collection = $collection;
	}

	/**
	 * Изменить модель итерации
	 *
	 * @param Model $current
	 */
	public function setCurrent($current)
	{
		$this->current = $current;
	}

	/**
	 * Изменить данные для итерации
	 *
	 * @param array $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * Изменить индекс итерации
	 *
	 * @param integer $index
	 */
	public function setIndex($index)
	{
		$this->index = $index;
	}

	/**
	 * @inheritdoc
	 */
	public function valid()
	{
		return isset($this->data[$this->index]);
	}
}