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
     * Конструктор
     * 
     * @param array $data
     */
    public function __construct($collection)
    {
        $this->index = 0;
        $this->data = $collection;
    }
    
	/**
	 * @inheritdoc
	 */
	public function current()
	{
        $modelManager = IcEngine::serviceLocator()->getService('modelManager');
        $modelName = $this->data->modelName();
        $keyField = $this->data->keyField();
        $item = $this->data->item($this->index);
        $model = $modelManager->get(
            $modelName, $item[$keyField], $item
        );
		return $model;
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