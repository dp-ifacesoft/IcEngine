<?php

/**
 * Форма
 *
 * @author markov
 */
class Form implements IteratorAggregate
{
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
     * Проверяет проходит ли валидацию
     * 
     * @return boolean
     */
    public function validate()
    {
        foreach ($this->elements as $element) {
            if (!$element->validate()) {
                return false;
            }
        }
        return true;
    }
}
