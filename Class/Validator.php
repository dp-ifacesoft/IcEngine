<?php

/**
 * Абстрактный валидатор
 *
 * @author markov
 */
abstract class Validator
{
    /**
     * Валидируемые данные
     *
     * @var Mixed
     */
    protected $data;

    /**
     * Название валидатора данных
     */
    protected $dataValidator = '';

    /**
     * Объект ошибки валидации для данного валидатора
     *
     * @var Validator_Error
     */
    protected $validatorError = null;

    /**
     * Параметры
     *
     * @var array
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
     * Получить валидируемые данные
     *
     * @return Mixed
     */
    public function getData()
    {
        return $this->data;
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
     *  Получить сервис
     */
    public function getService($name)
    {
        return IcEngine::serviceLocator()->getService($name);
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
     * @return array $params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Установить валидируемые данные
     *
     * @param Mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
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
     * @return boolean
     */
    public function validate()
    {
        $data = $this->getData();
        return $this->getDataValidator()->validate($data);
    }
}
