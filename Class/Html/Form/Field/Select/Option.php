<?php
/**
 * Опшен для выбора в Html-поле Select
 *
 * Изначально планировалось сделать его наследником Value_Object Романа Маркова,
 * но тут требуется метод setSelected(...), а изменение хранимых данных
 * противоречит концепции Value_Object. Так что сорри. Пусть это будет самостоятельный класс.
 *
 * @author LiverEnemy
 */

class Html_Form_Field_Select_Option
{
    /**
     * Выбран ли данный опшен
     *
     * @var boolean
     */
    protected $_isSelected = FALSE;

    /**
     * Текст данного опшена
     *
     * @var string
     */
    protected $_text;

    /**
     * Значение опшена, которое будет отправлено на сервер, если данный опшен выбран
     *
     * @var string
     */
    protected $_value;

    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * Получить полный текст для показа пользователю
     *
     * Аттеншен: данный текст может быть переопределен заданием необязательного атрибута label
     * @link http://htmlbook.ru/html/option/label (подробнее)
     *
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Получить значение value, которое будет отправлено из формы на сервер
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Проверить, является ли данный опшен выбранным
     *
     * @return bool
     */
    public function isSelected()
    {
        return $this->_isSelected;
    }

    /**
     * Выбрать данный опшен
     *
     * @return $this
     */
    public function select()
    {
        return $this->setSelected(TRUE);
    }

    /**
     * Инициализировать опшен данными
     *
     * @param array $data Данные для инициализации опшена
     * @return $this
     */
    public function setData(array $data = [])
    {
        foreach ($data as $index => $value) {
            $methodName = "set" . ucfirst($index);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
        return $this;
    }

    /**
     * Выбрать или отменить выбор данного опшена
     *
     * @param boolean $selected Выбрать или отменить выбор
     * @return $this
     */
    public function setSelected($selected)
    {
        $this->_isSelected = (bool) $selected;
        return $this;
    }

    /**
     * Задать текст опшена
     *
     * @param string $text
     * @return $this
     * @throws Exception Если предоставлено что-то отличное от строки
     */
    public function setText($text)
    {
        if (!is_string($text) && !empty($text)) {
            throw new Exception(__METHOD__ . ' requires a parameter to be a string or NULL');
        }
        $this->_text = $text;
        return $this;
    }

    /**
     * Задать значение опшена
     *
     * @param string $value
     * @return $this
     * @throws Exception
     */
    public function setValue($value)
    {
        if (!empty($value) && !is_string($value)) {
            throw new Exception(__METHOD__ . ' requires a param to be a string or NULL');
        }
        $this->_value = $value;
        return $this;
    }

    /**
     * Отменить выбор данного опшена
     *
     * @return $this
     */
    public function unSelect()
    {
        return $this->setSelected(FALSE);
    }
}