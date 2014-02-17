<?php

/**
 * Форма
 *
 * @author markov
 */
class Form implements IteratorAggregate
{
    /**
     * Название формы
     */
    public $name;
    
    /**
     * Элементы формы
     */
    public $elements = array();
    
    /**
     * Атрибуты формы
     */
    public $attributes = array();
    
    /**
     * Добавить элемент формы
     */
    public function add(Form_Element $element)
    {
        $this->elements[] = $element;
    }
    
    /**
     * Очищает все элементы формы
     */
    public function clear()
    {
        $this->elements = array();
    }
    
    /**
     * Получить элементы
     */
    public function elements()
    {
        return $this->elements();
    }
    
    /**
     * @inheritdoc
     */
    public function getIterator() 
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Устанавливает атрибут
     * 
     * @param string $name название атрибута
     * @param string $value значение 
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Устанавливает атрибуты
     * 
     * @param array $attributes атрибуты
     */
    public function setAttributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }
    
    /**
     * @param String $name название формы
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
