<?php
/**
 * Текстовый фильтр для моделей
 *
 * На входе должно быть строковое значение
 */

abstract class Model_Collection_Filter_Text extends Model_Collection_Filter
{
    /**
     * Подпись к элементу ввода фильтра
     *
     * @var string
     */
    protected $_label;

    /**
     * Имя GET-параметра для фильтра
     *
     * Если не установлено в коде, то при создании объекта фильтра конструируется автоматически из имени класса фильтра.
     * Прикладному разработчику оставлена возможность указать это имя самостоятельно
     * на случай конфликтов имен GET-параметров или жестко прописанного имени в задании на разработку
     * (можете поверить: необходимость строго заданных имен GET-параметров уже бывала).
     *
     * @var string
     */
    protected $_name;

    /**
     * Название типа фильтра для выбора наиболее подходящего smarty-шаблона элемента ввода в пользовательском интерфейсе
     *
     * @var string
     */
    protected $_type='text';

    /**
     * Значение для фильтрации
     *
     * @var string
     */
    protected $_value;


    /**
     * Защита от дурака: проверка на установленные значения $name и $label в коде фильтра
     *
     * @throws Exception в случае, если не установлены все необходимые поля фильтра в программном коде
     */
    public function __construct()
    {
        parent::__construct();
        $label = $this->getLabel();
        if (!$label)
        {
            throw new Exception(__CLASS__ . ': $_label required for a filter was not set');
        }
        $name = $this->getName();
        if (!isset($name))
        {
            $className = get_class($this);
            $name = 'filter-' . substr($className, strlen('Model_Collection_Filter_'));
            $this->_setName($name);
        }
    }

    protected function _setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Установить значение для фильтрации
     *
     * @param string $value
     *
     * @return $this
     */
    protected function _setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * Получить подпись к элементу ввода данного фильтра на форме
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Получить имя GET-параметра с требуемыми данными для фильтрации
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Получить значение для фильтрации
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Установить входные данные
     *
     * @param Data_Transport $input
     *
     * @return $this
     * @throws Exception В случае, если входные данные не являются Data_Transport
     */
    public function setInput(Data_Transport $input)
    {
        parent::setInput($input);
        $name = $this->getName();
        /** @var Service_Data_Transport $serviceDataTransport */
        $serviceDataTransport = $this->getService('serviceDataTransport');
        $value = $serviceDataTransport->receiveFromHierarchical($input, $name);
        $this->_setValue($value);
        return $this;
    }
} 