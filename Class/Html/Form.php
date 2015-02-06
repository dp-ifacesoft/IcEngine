<?php
/**
 * HTML-форма
 *
 * @author LiverEnemy
 */

class Html_Form
{
    /**
     * Поля ввода Html-формы
     *
     * @var Html_Form_Field[]
     */
    protected $_fields = [];

    /**
     * Входные данные для фильтрации
     *
     * @var Data_Transport
     */
    protected $_input;

    /**
     * @param string        $name   Часть имени класса Html-поля формы
     * @param string|NULL   $index  Индекс Html-поля для добавления
     * @return $this
     */
    protected function _addField($name, $index = NULL)
    {
        $fieldManager = App::htmlFormFieldManager();
        $newField = $fieldManager->get($name);
        $newField->setForm($this);
        if (empty($index)) {
            $fields = $this->getFields();
            $index = count($fields);
        }
        $this->_fields[$index] = $newField;
        return $this;
    }

    /**
     * Очистить массив Html-полей формы
     *
     * @return $this
     */
    protected function _clearFields()
    {
        $this->_fields = [];
        return $this;
    }

    /**
     * Получить все поля Html-формы
     *
     * @return Html_Form_Field[]
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Получить входной Data_Transport для получения данных
     *
     * @return Data_Transport
     * @throws Exception В случае, если
     */
    public function getInput()
    {
        if (empty($this->_input)) {
            throw new Exception(__METHOD__ . ' requires an input Data_Transport to be set');
        }
        return $this->_input;
    }

    /**
     * Получить "готовые" Html-поля формы
     *
     * @return array|Html_Form_Field[]
     */
    public function getReady()
    {
        $fields = $this->getFields();
        $result = [];
        foreach ($fields as $index => $htmlFormField) {
            if ($htmlFormField->isReady()) {
                $result[$index] = $htmlFormField;
            }
        }
        return $result;
    }

    /**
     * Получить ассоциативный массив всех значений полей формы
     *
     * @return array
     */
    public function getValues()
    {
        $result = [];
        $fields = $this->getReady();
        foreach ($fields as $index => $field) {
            if ($field->hasValue()) {
                $result[$index] = $field->getValue();
            }
        }
        return $result;
    }

    /**
     * Заполнено ли хоть одно поле формы
     *
     * @return bool
     */
    public function hasValues()
    {
        $fields = $this->getReady();
        foreach ($fields as $field) {
            if ($field->hasValue()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Все ли поля формы заполнены
     *
     * @return bool
     */
    public function hasAllValues()
    {
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if (!$field->isReady() || !$field->hasValue()) {
                return FALSE;
            }
        }
        return true;
    }

    /**
     * Инициализировать форму
     *
     * @return $this
     */
    public function init()
    {
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $field->init();
        }
        return $this;
    }

    /**
     * Задать массив Html-полей формы
     *
     * @param string[] $fields Массив требуемых имен классов полей формы
     * @return $this
     */
    public function setFields(array $fields = [])
    {
        $this->_clearFields();
        foreach ($fields as $index => $field) {
            $this->_addField($field, $index);
        }
        $formFields = $this->getFields();
        foreach ($formFields as $formField) {
            $formField->setContractors($formFields);
        }
        return $this->init();
    }

    /**
     * Установить входные данные для фильтрации
     *
     * @param Data_Transport $input
     *
     * @return $this
     */
    public function setInput(Data_Transport $input)
    {
        $this->_input = $input;
        return $this;
    }

}