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
     *
     * @var <Form_Validator_Error>
     */
    protected $formValidatorError = null;
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
        return $this->formValidatorError->errorMessage($value);
    }
    
    /**
     * @return Form_Validator_Error
     */
    public function getFormValidatorError()
    {
        if (!$this->formValidatorError) {
            $this->formValidatorError = new Form_Validator_Error();
            $this->formValidatorError->setParams($this->params);
        } 
        return $this->formValidatorError;
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
     * Устанавливает ошибку валидации
     * 
     * @param Form_Validator_Error $error
     */
    public function setFormValidatorError(Form_Validator_Error $error)
    {
        $this->formValidatorError = $error;
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
