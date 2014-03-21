<?php

/**
 * Абстрактный валидатор
 *
 * @author markov
 */
abstract class Validator 
{
    /**
     * Название валидатора данных
     */
    protected $dataValidator = '';
    
    /**
     *
     * @var <Validator_Error>
     */
    protected $validatorError = null;
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
        return $this->validatorError->errorMessage($value);
    }
    
    /**
     * @return Validator_Error
     */
    public function getValidatorError()
    {
        $locator = IcEngine::serviceLocator();
        if (!$this->validatorError) {
            $className = get_class($this);
            $validatorName = substr($className, strlen('Validator_'));
            $this->validatorError = $locator->getService(
                'validatorErrorManager'
            )
                ->get($validatorName);
            $this->validatorError->setParams($this->params);
        }
        if (!$this->validatorError) {
            $this->validatorError = new Validator_Error();
            $this->validatorError->setParams($this->params);
        } 
        return $this->validatorError;
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
            $validatorName = substr($className, strlen('Validator_'));
        }
        $dataValidator = $locator->getService('dataValidatorManager')
            ->get($validatorName);
        return $dataValidator;
    }
    
    /**
     * Устанавливает ошибку валидации
     * 
     * @param Validator_Error $error
     */
    public function setValidatorError(Validator_Error $error)
    {
        $this->validatorError = $error;
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
        if (is_string($params)) {
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
