<?php

/**
 * Абстрактный класс валидатора
 * 
 * @author goorus, morph
 */
abstract class Data_Validator_Abstract 
{	
    /**
     * Аргументы
     * 
     * @var array
     * @Generator
     */
    protected $params;
    
	/**
	 * Валидация строки
	 * 
     * @param string $data Данные.
	 * @return true|string
	 * 		true, если данные прошли валидацию или 
	 * 		строка ошибки.
	 */
	public function validate($data, $value = null)
	{
		return true;
	}
    
    /**
     * Getter for "params"
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
        
    /**
     * Setter for "params"
     *
     * @param array params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }   
}