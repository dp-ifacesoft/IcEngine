<?php

/**
 * Пул валидаторов
 * 
 * @author liverenemy
 * @Injectable
 */
class Validator_Pool
{
    /**
     * Данные, с которыми работает
     * 
     * @var Mixed
     */
    protected $data;

    /**
     * Ошибки в процессе проверки
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Параметры проверки
     *
     * @var Mixed
     */
    protected $params;

    /**
     * Менеджер валидаторов
     * @var Manager_Simple
     * @Inject("validatorManager")
     */
    protected $validatorManager;

    /**
     * Массив конкретных валидаторов
     *
     * @var array
     */
    protected $validators = array();

    /**
     * Добавить валидатор для проверки входных данных
     * @param Validator $validator Экземпляр валидатора
     * @return $this
     */
    protected function addValidator(Validator $validator)
    {
        if (!in_array($validator, $this->validators)) {
            $this->validators[] = $validator;
        }
        return $this;
    }

    /**
     * Очистить все сообщения об ошибках
     * @return $this
     */
    protected function clearErrors()
    {
        $this->errors = array();
        return $this;
    }

    /**
     * Очистить валидаторы
     * @return $this
     */
    protected function clearValidators()
    {
        $this->validators = array();
        return $this;
    }

    /**
     * Получить массив ошибок валидации
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Получить массив всех сообщений об ошибке
     */
    public function errorMessages()
    {
        $errorMessages = array();
        foreach ($this->errors as $error) {
            /** @var Validator_Error $error */
            $errorMessages[] = $error->errorMessage();     
        }
        return $errorMessages;
    }

    /**
     * Получить первую ошибку валидации
     * @return Validator_Error|null
     */
    public function error()
    {
        $errors = $this->errors();
        return count($errors) > 0 ? $errors[0] : null;
    }

    /**
     * Результат прохождения валидации
     * @return bool Успех валидации
     */
    public function isOk()
    {
        return empty($this->errors);
    }

    /**
     * Получить входные данные
     * @return Mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Получить текущие параметры пула валидаторов
     *
     * @return Mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Получить экземпляр сервиса
     * @param String $name Название требуемого сервиса
     * @return mixed
     */
    protected function getService($name)
    {
        return IcEngine::serviceLocator()->getService($name);
    }

    /**
     * Установить входные данные
     * @param Mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Установить параметры пула вадидаторов
     *
     * @param Mixed $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Установить валидаторы для проверки
     * @param array $validatorNames Классы валидаторов
     * @return $this
     */
    public function setValidators(array $validatorNames)
    {
        $this->clearValidators();
        foreach ($validatorNames as $name) {
            $this->addValidator($this->validatorManager->get($name));
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate($collectAllErrors = false)
    {
        $this->clearErrors();
        $data = $this->getData();
        $params = $this->getParams();
        foreach ($this->validators as $validator) {
            /** @var Validator $validator */
            $validator->setParams($params);
            $validator->setData($data);
            if (!$validator->validate()) {
                $this->errors[] = $validator->getValidatorError();
                if (!$collectAllErrors) {
                    break;
                }
            }
        }
        return $this;
    }
} 