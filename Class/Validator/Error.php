<?php


/**
 * Ошибки валидации
 *
 * @author markov
 */
class Validator_Error 
{ 
    /**
     * Параметры
     */
    protected $params = array();
    
    /**
     * Получить параметры
     * 
     * @param array $params
     */
    public function getParams() 
    {
        return $this->params;
    }
    
    /**
     * Устанавливает параметры
     * 
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
    
    /**
     * Возвращает код ошибки
     * 
     * @param mixed $value
     * @return string
     */
    public function errorCode()
    {
        return 'errorCode';
    }
    
    /**
     * Возвращает текст ошибки
     * 
     * @param mixed $value
     * @return string
     */
    public function errorMessage($value = null)
    {
        return 'Ошибка валидации';
    }
}
