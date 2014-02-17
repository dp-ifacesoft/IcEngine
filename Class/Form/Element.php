<?php

/**
 * Элемент формы
 *
 * @author markov
 */
abstract class Form_Element 
{
    /**
     * Имя поля
     */
    public $name;

    /**
     * Аттрибуты
     */
    public $attributes = array();
    
    /**
     * Валидаторы
     */
    public $validators = array();
    
    /**
     * Получает тип элемента формы
     * 
     * @return string
     */
    public function getType()
    {
        $className = get_class($this);
        return strtolower(substr($className, strlen('Form_Element_')));
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
     * Устанавливает валидаторы
     */
    public function setValidators($validators)
    {
        $this->validators = array_merge($this->validators, $validators);
    }
    
    /**
     * Устанавливает название
     * 
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}