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

    protected $modelName;
    
    protected $keyField;
    
    protected $modelManager;
    
    /**
     * Конструктор
     * 
     * @param array $data
     */
    public function __construct($collection)
    {
        $this->index = 0;
        $this->data = $collection;
        $this->modelManager = IcEngine::serviceLocator()->getService('modelManager');
        $this->modelName = $collection->modelName();
        $this->keyField = $collection->keyField();
    }
    
	/**
	 * @inheritdoc
	 */
	public function current()
	{
        $item = $this->data->item($this->index);
        $model = $this->modelManager->get(
            $this->modelName, $item[$this->keyField], $item
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