<?php

/**
 * Простой итератор коллекции
 *
 * @author morph
 */
class Model_Collection_Iterator_Array extends ArrayIterator
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
     * Состояние итерации
     * 
     * @var Model_Collection_Iterator_Array_State
     */
    private $state;

    /**
     * Конструктор
     * 
     * @param array $data
     */
    public function __construct($collection)
    {
        $this->index = 0;
        $this->data = $collection;
        $this->state = new Model_Collection_Iterator_Array_State($this);
    }
    
	/**
	 * @inheritdoc
	 */
	public function current()
	{
		return $this->state;
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
	 * Изменить данные для итерации
	 *
	 * @param array $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}
    
	/**
	 * @inheritdoc
	 */
	public function valid()
	{
		return isset($this->data->getItems()[$this->index]);
	}
}