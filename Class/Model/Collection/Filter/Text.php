<?php
/**
 * Текстовый фильтр для моделей
 *
 * На входе должно быть строковое значение
 */

abstract class Model_Collection_Filter_Text extends Model_Collection_Filter
{


    /**
     * Название типа фильтра для выбора наиболее подходящего smarty-шаблона элемента ввода в пользовательском интерфейсе
     *
     * @var string
     */
    protected $_type='text';

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