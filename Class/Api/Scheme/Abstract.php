<?php

/**
 * Абстрактная схема api
 * 
 * @author morph
 */
abstract class Api_Scheme_Abstract
{
    /**
     * Измененные параметры схемы
     * 
     * @var array
     */
    protected $params;
    
    /**
     * Схема api
     * 
     * @var array
     */
    protected $scheme;
    
    /**
     * Текущий транспорт схемы
     * 
     * @var Api_Transport_Abstract
     */
    protected $transport;
    
    /**
     * Название транспорта для схемы
     * 
     * @var string
     */
    protected $transportName;
    
    /**
     * Магический вызов
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!isset($this->scheme[$method])) {
            return null;
        }
        $methodScheme = $this->scheme[$method];
        $resultArgs = array();
        if (!empty($methodScheme['args'])) {
            foreach ($methodScheme['args'] as $i => $arg) {
                $resultArgs[$arg] = is_numeric($i) && isset($args[$i])
                    ? $args[$i] : $arg;
            } 
        }
        if ($this->params) {
            foreach ($this->params as $paramName => $value) {
                $resultArgs[$paramName] = $value;
            }
        }
        return $this->transport->send($methodScheme['call'], $resultArgs);
    }
    
    /**
     * Изменить параметр схемы
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    /**
     * Получить имя транспорта
     * 
     * @return string
     */
    public function getTransportName()
    {
        return $this->transportName;
    }
    
    /**
     * Изменить текущий транспорт
     * 
     * @param Api_Transport_Abstract $transport
     */
    public function setTransport($transport)
    {
        $this->transport = $transport; 
    }
}