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
    public $name;

    /**
     * Значение
     */
    public $value;
    
    /**
     * Дополнительные данные
     * 
     * @var array
     */
    public $data = array();
    
    /**
     *
     * @var Form
     */
    public $form = null;
    
    /**
     * Ошибки после валидации формы
     */
    public $errors = array();
    
    /**
     * Аттрибуты
     */
    public $attributes = array();
    
    /**
     * Валидаторы
     */
    public $validators = array();
    
    /**
     * Выбираемые данные (select)
     */
    public $selectable = array();
    
    /**
     * Получить дополнительные данные
     * 
     * @return array
     */
    public function data()
    {
        return $this->data;
    }
    
    /**
     * Установить дополнительные данные
     * 
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Получает тип элемента формы
     * 
     * @return string
     */
    public function getType()
    {
        $className = get_class($this);
        return lcfirst(substr($className, strlen('Form_Element_')));
    }
    
     /**
     * Устанавливает атрибут
     * 
     * @param string $name название атрибута
     * @param string $value значение 
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Устанавливает атрибуты
     * 
     * @param array $attributes атрибуты
     */
    public function setAttributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }
    
    /**
     * Устанавливает валидаторы
     */
    public function setValidators($validators)
    {
        $locator = IcEngine::serviceLocator();
        $validatorManager = $locator->getService('validatorManager');
        $validatorErrorManager = $locator->getService('validatorErrorManager');
        foreach ($validators as $key => $item) {
            $validatorName = $key;
            if (!is_string($key)) {
                $validatorName = $item;
                $item = array();
            }
            $validator = $validatorManager->get($validatorName);
            $validator->setParams($item);
            $formName = $this->getForm()->getName();
            if ($formName) {
                $validatorError = $validatorErrorManager
                    ->get(ucfirst($formName) . '_' . $validatorName);
            }
            if (!$validatorError) {
                $validatorError = $validatorErrorManager->get($validatorName);
            }
            if ($validatorError) {
                $validatorError->setParams($validator->getParams());
                $validator->setValidatorError($validatorError);
            }
            $this->validators[] = $validator;
        }
    }
    
    /**
     * Устанавливает форму
     * 
     * @param String $name
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
    }
    
    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
    
    /**
     * Устанавливает название
     * 
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Устанавливает значение
     * 
     * @param array $value значение
     */
    public function setSelectable($values)
    {
        $this->selectable = $values;
    }
    
    /**
     * Устанавливает значение
     * 
     * @param mixed $value значение
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
     * Возвращает значение
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Валидирует елемент формы
     * 
     * @return boolean
     */
    public function validate()
    {
        $result = true;
        foreach ($this->validators as $validator) {
            /** @var Validator $validator */
            $validator->setData($this->value);
            $isValidate = $validator->validate();
            if (!$isValidate) {
                $this->errors[] = $validator->errorMessage($this->value);
                $result = false;
            }
        }
        return $result;
    }
    
}