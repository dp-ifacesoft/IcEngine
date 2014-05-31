<?php

/**
 * Состояние итерация текущего элемента коллекции
 * 
 * @author morph
 */
class Model_Collection_Iterator_Array_State implements ArrayAccess
{
    /**
     * Итератор
     * 
     * @var 
     * @var Model_Collection_Iterator_Array $iterator
     */
    protected $iterator;
    
    /**
     * Конструктор
     * 
     * @param Model_Collection_Iterator_Array $iterator
     */
    public function __construct($iterator)
    {
        $this->iterator = $iterator;
    }
    
    /**
     * Получить значение по ключу
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->iterator->getData()->item($this->iterator->key())[$key];
    }
    
    /**
     * Изменить значение по ключу
     * 
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value) 
    {
        $this->iterator->getData()->item($this->iterator->key())[$key] = $value;
  
    }
    
    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        $item = $this->iterator->getData()->item($this->iterator->key());
        return isset($item[$offset]);
    }
    
    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }
    
    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }
    
    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        
    }
}