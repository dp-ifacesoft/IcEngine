<?php
/**
 * Абстрактный фильтр коллекций моделей
 *
 * @author LiverEnemy
 */

abstract class Model_Collection_Filter extends Html_Form_Field
{
    /**
     * @var Model_Collection    $_collection    Фильтруемая коллекция
     */
    protected $_collection;

    /**
     * @var Model_Collection    $_result        Результат фильтрации
     */
    protected $_result;

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
} 