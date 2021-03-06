<?php

/**
 * Форма
 *
 * @author markov
 */
class Form implements IteratorAggregate
{
    /**
     * Валидная ли форма
     */
    public $isValid = true;
    
    /**
     * Название формы
     */
    public $name;
    
    /**
     * Элементы формы
     */
    public $elements = array();
    
    /**
     * Атрибуты формы
     */
    public $attributes = array();
    
    /**
     * Добавить элемент формы
     */
    public function add(Form_Element $element)
    {
        foreach ($this->elements as $key => $item) {
            if ($item->name == $element->name) {
                $this->elements[$key] = $element;
                return;
            }
        }
        $this->elements[] = $element;
    }
    
    /**
     * Возвращает данные с формы
     * 
     * @return array
     */
    public function getData()
    {
        $result = array();
        foreach ($this->elements as $element) {
            $result[$element->name] = $element->getValue();
        }
        return $result;
    }
    
    /**
     * Присоединяет данные с формы
     * 
     * @param array $request
     */
    public function bind($request)
    {
        foreach($this->elements as $element) {
            if (!isset($request[$element->name])) {
                continue;
            }
            $element->setValue($request[$element->name]);
        }
    }
    
    /**
     * Очищает все элементы формы
     */
    public function clear()
    {
        $this->elements = array();
    }
    
    /**
     * Получить елемент по имени
     * 
     * @param string $name название элемента
     * @return Form_Element
     */
    public function element($name)
    {
        foreach ($this->elements as $element) {
            if ($element->name == $name) {
                return $element;
            }
        }
        return null;
    }
    
    /**
     * Получить элементы
     */
    public function elements()
    {
        return $this->elements;
    }
    
    /**
     * @inheritdoc
     */
    public function getIterator() 
    {
        return new ArrayIterator($this->elements);
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
     * @param String $name название формы
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Проверяет проходит ли валидацию
     * 
     * @return boolean
     */
    public function validate()
    {
        $isValid = true;
        foreach ($this->elements as $element) {
            if (!$element->validate()) {
                $isValid = false;
            }
        }
        $this->isValid = $isValid;
        return $isValid;
    }
    
    /**
     * Возвращает последнюю ошибку валидации
     * 
     * @return string
     */
    public function getLastErrorMessage()
    {
        foreach ($this->elements as $element) {
            if (!$element->errors) {
                continue;
            }
            return $element->errors[0];
        }
        return '';
    }
    
    /**
     * Получить все ошибки
     * 
     * @return array
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->elements as $element) {
            if (!$element->errors) {
                continue;
            }
            $errors[$element->name] = $element->errors;
        }
        return $errors;
    }
}

