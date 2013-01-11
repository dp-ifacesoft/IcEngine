<?php

/**
 * Ресурс состояния схемы связей модели
 * 
 * @author morph
 */
class Model_Mapper_Scheme_Resource
{
	/**
	 * Полученные в ресурс модели
	 * 
     * @var array
	 */
	protected $items;

	/**
	 * Ссылка
	 * 
     * @var Model_Mapper_Scheme_Reference_Abstract
	 */
	protected $reference;

    /**
     * Проксирующий вызов метода
     * 
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if (is_object($this->items)) {
            $methodReflection = new \ReflectionMethod($this->items, $method);
            return $methodReflection->invokeArgs($this->items, $args);
        }
    }
    
    /**
     * Конструктор
     * 
     * @param Model_Mapper_Scheme_Reference_Abstrac $reference
     */
	public function __construct($reference)
	{
		$this->reference = $reference;
	}

	/**
	 * Добавить модель для сохранения
	 * 
     * @param array $items
	 */
	public function add($item)
    {
        
    }

	/**
	 * Добавить модель
	 * 
     * @param array $item
	 */
	public function addItem($item)
	{
		$this->items[] = $item;
	}

	/**
	 * Удалить модель
	 * 
     * @param array $item
	 */
	public function delete($item)
	{

	}

	/**
	 * Получить первую модель
	 * 
     * @return Model
	 */
	public function get()
	{
		return $this->items[0];
	}

	/**
	 * Получить модели
	 * 
     * @return array
	 */
	public function items()
	{
		return $this->items;
	}
    
    /**
	 * Передать модели
	 * 
     * @param array $items
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}
}