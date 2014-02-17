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
     * Создать форму
     * 
     * @param Dto
     * @return Form
     */
    public function create(Dto $dto = null)
    {
        foreach ($dto->getFields() as $field) {
            $field['options'] = isset($field['options']) ? 
                $field['options'] : array();
            $this->add($field['name'], $field['type'], $field['options']);
        }
        return $this->getForm();
    }
    
    /**
     * Добавить элемент к форме
     * 
     * @param string $name имя поля
     * @param string $elementType тип элемента
     * @param array $options дополнительные параметры
     * @return $this
     */
    public function add($name, $elementType, $options = array())
    {
        $formElement = $this->formElementManager->get(ucfirst($elementType));
        $formElement->setName($name);
        $formElement->setOptions($options);
        $this->form->add($formElement);
        return $this;
    }
    
    /**
     * Устанавливает атрибут
     * 
     * @param string $name название атрибута
     * @param string $value значение 
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->form->setAttribute($name, $value);
        return $this;
    }
    
    /**
     * Устанавливает атрибуты
     * 
     * @param array $attributes атрибуты
     * @return $this
     */
    public function setAttributes($attributes)
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
    public function setName($name)
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
