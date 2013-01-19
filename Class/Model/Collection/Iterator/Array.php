<?php

/**
 * Простой итератор коллекции
 *
 * @author morph
 */
class Model_Collection_Iterator_Array implements Iterator
{
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
     * Конструктор
     * 
     * @param array $data
     */
    public function __construct($collection)
    {
        $this->index = 0;
        $this->data = $collection->items();
    }
    
	/**
	 * @inheritdoc
	 */
	public function &current()
	{
		return $this->data[$this->index];
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
	 * @inheritdoc
	 */
	public function valid()
	{
		return isset($this->data[$this->index]);
	}
}