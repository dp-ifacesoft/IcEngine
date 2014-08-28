<?php
/**
 * Сервис работы с Model_Collection_Filter_Pool
 *
 * @author LiverEnemy
 */

class Service_Filter_Pool extends Service_Abstract
{
    /**
     * @param Model_Collection $collection  Текущая коллекция
     * @param Data_Transport   $input       Транспорт входных данных
     * @param string           $type        Тип конфига: 'admin' - для админки
     *
     * @return Model_Collection_Sorter
     */
    protected function _createCollectionSorter(Model_Collection $collection, Data_Transport $input, $type)
    {
        $table          = $collection->table();
        $config         = $this->_getConfig($table, $type);
        $fields         = !empty($config['sort']['fields'])         ? $config['sort']['fields']         : '';
        $fieldParamName = !empty($config['sort']['fieldParamName']) ? $config['sort']['fieldParamName'] : '';
        $orderParamName = !empty($config['sort']['orderParamName']) ? $config['sort']['orderParamName'] : '';
        /** @var Model_Collection_Sorter $modelCollectionSorter */
        $modelCollectionSorter = $this->getService('modelCollectionSorter');
        $modelCollectionSorter
            ->setCollection($collection)
            ->setFields($fields)
            ->setFieldParamName($fieldParamName)
            ->setInput($input)
            ->setOrderParamName($orderParamName)
        ;
        return $modelCollectionSorter;
    }

    /**
     * Получить конфиг для таблицы
     *
     * @param   string  $table  Таблица для нахождения конфига
     * @param   string  $type   Тип конфига: 'admin' - для админки
     *
     * @return  array
     */
    protected function _getConfig($table, $type)
    {
        /** @var Config_Manager $configManager */
        $configManager = $this->getService('configManager');
        $config = $configManager->get('Model_Collection_Filter_' . $table);
        if ($config->offsetExists($type))
        {
            return $config->offsetGet($type)->__toArray();
        }
        return [];
    }

    /**
     * Получить список полей для фильтрации
     *
     * @param   string  $table  Таблица
     * @param   string  $type   Для чего нужны данные
     *
     * @return  array
     */
    protected function _getFilterNames($table, $type)
    {
        $config = $this->_getConfig($table, $type);
        return $config['filters'] ?: [];
    }

    /**
     * Получить Фильтр-Пул определенного типа для коллекции и транспорта входных данных
     *
     * @param Model_Collection $collection  Коллекция, на которую накладывается фильтр-пул
     * @param Data_Transport   $input       Входной Data_Transport
     * @param string           $type        Тип конфига: 'admin' - для админки
     *
     * @return Model_Collection_Filter_Pool
     */
    public function getFor(Model_Collection $collection, Data_Transport $input, $type)
    {
        $table = $collection->table();
        $filters = $this->_getFilterNames($table, $type);
        $collectionSorter = $this->_createCollectionSorter($collection, $input, $type);
        /** @var Model_Collection_Filter_Pool $filterPool */
        $filterPool = $this->getService('modelCollectionFilterPool');
        $filterPool
            ->setInput($input)
            ->setCollection($collection)
            ->setCollectionSorter($collectionSorter)
            ->setFilters($filters)
            ->apply();
        return $filterPool;
    }
} 