<?php
/**
 * Пул фильтров на коллекции моделей
 *
 * @author LiverEnemy
 */

class Model_Collection_Filter_Pool extends Html_Form
{
    /**
     * Фильтруемая коллекция
     *
     * @var Model_Collection $_collection
     */
    protected $_collection;

    /**
     * Массив фильтров для коллекции
     *
     * @var array $_filters
     */
    protected $_filters = [];

    /**
     * Сортировщик коллекции
     *
     * @var Model_Collection_Sorter
     */
    protected $_collectionSorter;

    /**
     * Результат фильтрации
     *
     * @var Model_Collection $_result
     */
    protected $_result;

    /**
     * Добавить фильтр к пулу
     *
     * @param   Model_Collection_Filter $filter
     *
     * @return  $this
     */
    protected function _addFilter(Model_Collection_Filter $filter)
    {
        $this->_filters[] = $filter;
        return $this;
    }

    /**
     * Сбросить установленные фильтры
     *
     * @return $this
     */
    protected function _clearFilters()
    {
        $this->_filters = [];
        return $this;
    }

    /**
     * Сохранить результат фильтрации
     *
     * @param Model_Collection $result Задаваемый результат фильтрации
     *
     * @return $this
     */
    protected function _setResult(Model_Collection $result)
    {
        $this->_result = $result;
        return $this;
    }

    /**
     * Применить сортировку и фильтрацию
     *
     * @return $this
     */
    public function apply()
    {
        $collection = $this->getCollection();
        $filters = $this->getFilters();
        $this->_setResult($collection);
        /** @var Model_Collection_Filter $filter */
        foreach ($filters as $filter)
        {
            $result = $this->getResult();
            $filter
                ->init()
                ->setCollection($result)
                ->apply();
            $this->_setResult(
                $filter->getResult()
            );
        }
        $collectionSorter = $this->getCollectionSorter();
        $result = $this->getResult();
        $collectionSorter
            ->setCollection($result)
            ->apply();
        $result = $collectionSorter->getResult();
        $this->_setResult($result);
        return $this;
    }

    /**
     * Получить текущую коллекцию
     *
     * @return Model_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Получить сортировщик текущей коллекции
     *
     * @return Model_Collection_Sorter
     */
    public function getCollectionSorter()
    {
        return $this->_collectionSorter;
    }

    /**
     * Получить текущие фильтры
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
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
     * @param string $name Имя требуемого сервиса
     *
     * @return mixed
     */
    public function getService($name)
    {
        $serviceLocator = IcEngine::serviceLocator();
        return $serviceLocator->getService($name);
    }

    /**
     * @param Model_Collection $collection Коллекция для фильтрации
     *
     * @return $this
     */
    public function setCollection(Model_Collection $collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Установить сортировщик текущей коллекции
     *
     * @param Model_Collection_Sorter $collectionSorter Объект сортировщика
     *
     * @return $this
     */
    public function setCollectionSorter(Model_Collection_Sorter $collectionSorter)
    {
        $this->_collectionSorter = $collectionSorter;
        return $this;
    }

    /**
     * Задать фильтры для процесса фильтрации
     *
     * @param array $filterNames
     *
     * @return $this
     * @throws Exception
     */
    public function setFilters(array $filterNames)
    {
        /** @var Model_Collection_Filter_Manager $filterManager */
        $filterManager = $this->getService('modelCollectionFilterManager');
        if (!($filterManager instanceof Model_Collection_Filter_Manager))
        {
            throw new Exception(__METHOD__ . ' did not receive a correct instance of Filter_Manager');
        }
        $this->_clearFilters();
        foreach ($filterNames as $filterName)
        {
            /** @var Model_Collection_Filter $filter */
            $filter = $filterManager->get($filterName);
            $this->_addFilter($filter);
        }
        return $this;
    }
} 