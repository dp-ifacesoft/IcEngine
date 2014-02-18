<?php

/**
 * Построитель форм
 * 
 * @author markov
 * @Service("formBuilder", source={method="instance"}))
 * @Injectable
 */
class Form_Builder
{   
    /**
     * Текущий элемент
     */
    private $currentElement;
    
    protected $form;
    
    /**
     * @Inject("formElementManager")
     */
    protected $formElementManager;
    
    public function instance()
    {     
        $this->form = new Form();
        return $this;
    }
    
    /**
     * Добавить элемент к форме
     * 
     * @param string $name имя поля
     * @param string $elementType тип элемента
     * @param array $options дополнительные параметры
     * @return $this
     */
    public function add($name, $elementType)
    {
        $formElement = $this->formElementManager->get(ucfirst($elementType));
        $formElement->setName($name);
        $this->form->add($formElement);
        $this->currentElement = $formElement;
        return $this;
    }
    
    /**
     * Создает форму по dto
     * 
     * @param Dto $dto
     * @return Form
     */
    public function create($dto)
    {
        $this->instance();
        if (isset($dto->formName)) {
            $this->setFormName($dto->formName);
        }
        if (isset($dto->formAttributes)) {
            $this->setFormAttributes($dto->formAttributes);
        }
        if ($dto->elements) {
            foreach ($dto->elements as $element) {
                reset($element);
                $name = key($element);
                $type = $element[$name];
                $this->add($name, $type);
                if (isset($element['attributes'])) {
                    $this->setAttributes($element['attributes']);
                }
                if (isset($element['validators'])) {
                    $this->setValidators($element['validators']);
                }
                if (isset($element['selectable'])) {
                    $this->setSelectable($element['selectable']);
                }
                if (isset($element['value'])) {
                    $this->setValue($element['value']);
                }
            }
        }
        return $this->getForm();
    }
 
    /**
     * Устанавливает атрибуты элемента
     * 
     * @param array $attributes атрибуты
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->currentElement->setAttributes($attributes);
        return $this;
    }
    
    /**
     * Устанавливает валидаторы формы
     * 
     * @param array $validators валидаторы
     * @return $this
     */
    public function setValidators($validators)
    {
        $this->currentElement->setValidators($validators);
        return $this;
    }
    
    /**
     * Устанавливает все значения для выбора элемента формы
     * 
     * @param array $value все значения
     * @return $this
     */
    public function setSelectable($values)
    {
        $this->currentElement->setSelectable($values);
        return $this;
    }
    
    /**
     * Устанавливает значение элемента формы
     * 
     * @param array $value активные значения
     * @return $this
     */
    public function setValue($value)
    {
        $this->currentElement->setValue($value);
        return $this;
    }
    
    /**
     * Устанавливает атрибут формы
     * 
     * @param string $name название атрибута
     * @param string $value значение 
     * @return $this
     */
    public function setFormAttribute($name, $value)
    {
        $this->form->setAttribute($name, $value);
        return $this;
    }
    
    /**
     * Устанавливает атрибуты формы
     * 
     * @param array $attributes атрибуты
     * @return $this
     */
    public function setFormAttributes($attributes)
    {
        $this->form->setAttributes($attributes);
        return $this;
    }
    
    /**
     * Устанавливает название
     * 
     * @param String $name название формы
     * @return $this
     */
    public function setFormName($name)
    {
        $this->form->setName($name);
        return $this;
    }
    
    /**
     * Получить форму
     * 
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
    
}
