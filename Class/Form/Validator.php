<?php

/**
 * Абстрактный валидатор
 *
 * @author markov
 */
abstract class Form_Validator 
{
    /**
     * Название валидатора данных
     */
    protected $dataValidator = '';
    
    /**
     * Параметры
     */
    protected $params = array();
    
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
    
    /**
     * @return Data_Validator_Abstract
     */
    public function getDataValidator() 
    {
        $locator = IcEngine::serviceLocator();
        if ($this->dataValidator) {
            $validatorName = $this->dataValidator; 
        } else {
            $className = get_class($this);
            $validatorName = substr($className, strlen('Form_Validator_'));
        }
        $dataValidator = $locator->getService('dataValidatorManager')
            ->get($validatorName);
        return $dataValidator;
    }
    
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
        if (!is_array($params)) {
            $this->params = array($params);
        } else {
            $this->params = $params;
        }
    }
    
    /**
     * Валидирует данные
     * 
     * @param mixed $value значение для проверки
     * @return boolean
     */
    public function validate($value) 
    {
       return $this->getDataValidator()->validate($value);
    }
}
