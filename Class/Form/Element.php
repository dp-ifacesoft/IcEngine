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
    protected $name;
    
    /**
     * Параметры
     */
    protected $options = array();
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function setOptions($options)
    {
        $this->options = $options;
    }
}