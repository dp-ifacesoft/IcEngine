<?php
/**
 * Абстрактный фильтр коллекций моделей
 *
 * @author LiverEnemy
 */

abstract class Model_Collection_Filter {
    /**
     * @var Model_Collection    $_collection    Фильтруемая коллекция
     */
    protected $_collection;

    /**
     * @var Mixed Данные для фильтрации
     */
    protected $_input;

    /**
     * @var Model_Collection    $_result        Результат фильтрации
     */
    protected $_result;

    /**
     * Тип фильтра для выбора наиболее подходящего элемента ввода в пользовательском интерфейсе
     *
     * @var string
     */
    protected $_type;

    /**
     * Защита от дурака - прикладного программиста.
     *
     * Дорогие коллеги! Для правильного выбора элемнета интерфейса ОБЯЗАТЕЛЬНО задавайте $_type в своих фильтрах!
     *
     * @throws Exception В случае, если в коде фильтра не прописан его тип
     */
    public function __construct()
    {
        $type = $this->getType();
        if (empty($type))
        {
            throw new Exception(__CLASS__ . ": filter type was not set");
        }
    }

    /**
     * Сохранить результат фильтрации
     *
     * @param Model_Collection $result Результат фильтрации
     *
     * @return $this
     */
    protected function _setResult(Model_Collection $result)
    {
        $this->_result = $result;
        return $this;
    }

    /**
     * Тело фильтра
     *
     * @return Model_Collection
     */
    abstract public function apply();

    /**
     * Получить оригинал фильтруемой коллекции
     *
     * @return Model_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Получить установленные ранее входные данные
     *
     * @return Mixed
     */
    public function getInput()
    {
        return $this->_input;
    }

    /**
     * Получить результат фильтрации
     *
     * @return Model_Collection
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Получить экземпляр сервиса
     *
     * @param $serviceName Имя требуемого сервиса
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        $serviceLocator = IcEngine::serviceLocator();
        return $serviceLocator->getService($serviceName);
    }

    /**
     * Получить тип фильтра для выбора элемента ввода на форме
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Задать коллекцию для фильтрации
     *
     * @param Model_Collection $collection Коллекция
     *
     * @return $this
     */
    public function setCollection(Model_Collection $collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Установить входные данные для фильтрации
     *
     * @param   Data_Transport  $input
     * @return  $this
     */
    public function setInput(Data_Transport $input)
    {
        $this->_input = $input;
        return $this;
    }
} 