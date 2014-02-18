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
     * Получает тип элемента формы
     * 
     * @return string
     */
    public function getType()
    {
        $className = get_class($this);
        return strtolower(substr($className, strlen('Form_Element_')));
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
        $formValidatorManager = $locator->getService('formValidatorManager');
        foreach ($validators as $key => $item) {
            $validatorName = $key;
            if (!is_string($key)) {
                $validatorName = $item;
                $item = array();
            }
            $validator = $formValidatorManager->get($validatorName);
            $validator->setParams($item);
            $this->validators[] = $validator;
        }
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
     * Валидирует елемент формы
     * 
     * @return boolean
     */
    public function validate()
    {
        foreach ($this->validators as $validator) {
            $isValidate = $validator->validate($this->value);
            if (!$isValidate) {
                return false;
            }
        }
        return true;
    }
    
}