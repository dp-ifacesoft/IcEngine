<?php

/**
 * Абстрактный Value Object
 *
 * @author markov
 */
abstract class Vo
{
    /**
     * @var array данные
     */
    protected $data = [];
    
    /**
     * 
     * @param array $data данные
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    /**
     * Возвращает все значения
     * 
     * @return array
     */
    public function get()
    {
        return $this->data;
    }
    
    /**
     * Возвращает значение поля
     * 
     * @return array
     */
    public function getField($name)
    {
        $field = isset($this->data[$name]) ? $this->data[$name] : null;
        return $field;
    }
    
    /**
     * @inheritdoc
     */
    public function __call($method, $args)
    {
        $regexp = '#get([a-zA-z]+)#';
        $matches = [];
        preg_match($regexp, $method, $matches);
        if (!$matches) {
            return;
        }
        $field = lcfirst($matches[1]);
        return $this->data[$field];
    }
}
