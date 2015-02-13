<?php
/**
 * Сортировщик коллекций для Model_Collection_Filter_Pool
 *
 * @author LiverEnemy
 */

class Model_Collection_Sorter
{
    /**
     * Сортировать по возрастанию
     */
    const SORTER_ASC = 0;

    /**
     * Сортировать по убыванию
     */
    const SORTER_DESC = 1;

    /**
     * Направление сортировки: SORT_ASC - по возрастанию, SORT_DESC - по убыванию
     *
     * @var bool
     */
    protected $_asc = self::SORTER_ASC;

    /**
     * Текущая сортируемая коллекция
     *
     * @var Model_Collection
     */
    protected $_collection;

    /**
     * Название поля, по которому упорядочивается коллекция
     *
     * @var string
     */
    protected $_field;

    /**
     * Названия полей для фильтрации
     *
     * Ассоциативный массив ['fieldName' => 'fieldComment']
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Ассоциативный массив подписей к полям текущей таблицы
     *
     * @var array
     */
    protected $_fieldTitles = [];

    /**
     * Название GET-параметра с именем поля сортировки
     *
     * @var string
     */
    protected $_fieldParamName;

    /**
     * Транспорт входных данных
     *
     * @var Data_Transport
     */
    protected $_input;

    /**
     * Название GET-параметра с направлением сортировки
     *
     * @var string
     */
    protected $_orderParamName;

    protected $_orderOptions = [
        [
            'title' => 'По возрастанию',
            'value' => self::SORTER_ASC,
        ],
        [
            'title' => 'По убыванию',
            'value' => self::SORTER_DESC,
        ]
    ];

    /**
     * Результат сортировки
     *
     * @var Model_Collection
     */
    protected $_result;

    /**
     * Таблица текущей коллекции
     *
     * @var string
     */
    protected $_table;

    /**
     * Получить заголовки полей текущей таблицы
     *
     * Заголовки полей будут получены только по тем полям, с которыми работает сортировщик ($this->_fields).
     *
     * @return array
     * @throws Exception в случае, если не установлена текущая таблица
     */
    protected function _getFieldTitles()
    {
        $table = $this->_getTable();
        if (empty($table))
        {
            throw new Exception(__METHOD__ . ' requires a table class field to be set');
        }
        if (!empty($this->_fieldTitles))
        {
            return $this->_fieldTitles;
        }
        /** @var Model_Scheme $serviceModelScheme */
        $serviceModelScheme = $this->getService('modelScheme');
        $scheme = $serviceModelScheme->scheme($table);
        if (!$scheme || empty($scheme['fields']))
        {
            throw new Exception(__METHOD__ . ': probably has not a correct Model_Mapper for ' . $table);
        }
        $schemeFields = $scheme['fields'];
        $fields = $this->getFields();
        $fieldTitles = [];
        foreach ($fields as $name)
        {
            $comment = '';
            if (isset($schemeFields[$name][1]['Comment']))
            {
                $comment = $schemeFields[$name][1]['Comment'];
            }
            $fieldTitles[$name] = $comment;
        }
        $this->_fieldTitles = $fieldTitles;
        return $this->_fieldTitles;
    }

    /**
     * Получить имя таблицы, коллекцию
     *
     * @return string
     */
    protected function _getTable()
    {
        return $this->_table;
    }

    /**
     * Установить поле для сортировки
     *
     * @param string $field
     *
     * @return $this
     */
    protected function _setField($field)
    {
        $this->_field = $field;
        return $this;
    }

    /**
     * Установить направление сортировки: TRUE для сортировки по возрастанию, FALSE - по убыванию
     *
     * @param boolean $order
     */
    protected function _setOrder($order)
    {
        $this->_asc = (bool) $order;
    }

    /**
     * Установить результат сортировки
     *
     * @param Model_Collection $result
     *
     * @return $this
     */
    protected function _setResult(Model_Collection $result)
    {
        $this->_result = $result;
        return $this;
    }

    /**
     * Задать имя текущей таблицы
     *
     * @param string $tableName Название таблицы
     *
     * @return $this
     * @throws Exception в случае попытки задать имя таблицы несуществующего класса
     */
    protected function _setTable($tableName)
    {
        if (!class_exists($tableName))
        {
            throw new Exception(__METHOD__ . ' requires a $tableName of existing table');
        }
        $this->_table = $tableName;
        return $this;
    }

    /**
     * Применить сортировку
     *
     * @return $this
     */
    public function apply()
    {
        $collection = $this->getCollection();
        $this->_setResult($collection);
        $this->_beforeApply();
        if ($this->_isOk())
        {
            $collection = $this->getCollection();
            $field = $this->getField();
            $order = $this->getOrder();
            $collection->addOptions(
                [
                    'name'  => ($order ? '::Order_Desc' : '::Order_Asc'),
                    'field' => $field,
                ]
            );
            $this->_setResult($collection);
        }
        return $this;
    }

    /**
     * Проверки перед применением сортировки
     *
     * @return $this
     * @throws Exception
     */
    protected function _beforeApply()
    {
        $orderParamFieldName = $this->orderParamName();
        $fieldParamFieldName = $this->fieldParamName();
        $collection = $this->getCollection();
        $input = $this->getInput();
        /** @var Service_Data_Transport $serviceDataTransport */
        $serviceDataTransport = $this->getService('serviceDataTransport');
        $field = $serviceDataTransport->receiveFromHierarchical($input, $fieldParamFieldName);
        $this->_setField($field);
        $order = $serviceDataTransport->receiveFromHierarchical($input, $orderParamFieldName);
        $this->_setOrder($order);
        if (!$collection || !$input)
        {
            throw new Exception(__METHOD__ . ': you MUST set the collection, field, input and order before sorting');
        }
        return $this;
    }

    /**
     * Проверить, установлены ли поле и порядок сортировки
     *
     * @return bool
     */
    protected function _isOk()
    {
        $field = $this->getField();
        $order = $this->getOrder();
        return $field && isset($order);
    }

    /**
     * Получить имя GET-параметра с названием текущего поля сортировки
     *
     * @return string
     */
    public function fieldParamName()
    {
        return $this->_fieldParamName ?: 'sort-field';
    }

    /**
     * Получить подпись к полю
     *
     * @param string $fieldName
     *
     * @return string
     * @throws Exception
     */
    public function fieldTitle($fieldName)
    {
        if (!in_array($fieldName, $this->getFields()))
        {
            throw new Exception(__METHOD__ . ' requires a field name to be in array of current sorter fields');
        }
        $labels = $this->_getFieldTitles();
        if (!empty($labels[$fieldName]))
        {
            return $labels[$fieldName];
        }
        return $fieldName;
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Получить имя текущего поля для сортировки
     *
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Получить список полей, по которым возможно осуществить сортировку
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Получить транспорт входных данных
     *
     * @return Data_Transport
     */
    public function getInput()
    {
        return $this->_input;
    }

    /**
     * Получить направление текущей сортировки
     *
     * @return bool
     */
    public function getOrder()
    {
        return $this->_asc;
    }

    /**
     * Получить результат сортировки
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
     * Варианты сортировки
     *
     * @return array
     */
    public function orderOptions()
    {
        return $this->_orderOptions;
    }

    /**
     * Получить имя GET-параметра с направлением сортировки
     *
     * @return string
     */
    public function orderParamName()
    {
        return $this->_orderParamName ?: 'sort-order';
    }

    /**
     * Задать коллекцию для сортировки
     *
     * @param Model_Collection $collection
     *
     * @return $this
     */
    public function setCollection(Model_Collection $collection)
    {
        $this->_collection = $collection;
        $this->_setTable($collection->table());
        return $this;
    }


    /**
     * Установить имя GET-параметра с названием поля сортировки
     *
     * @param string $name
     *
     * @return $this
     */
    public function setFieldParamName($name)
    {
        $this->_fieldParamName = (string) $name;
        return $this;
    }

    /**
     * Задать список полей, с которыми работает сортировщик
     *
     * Для применения сортировки не обязательно задавать весь список возможных полей заранее.
     * Список полей может потребоваться задавать только в том случае,
     * если список надо будет отобразить где-то на сайте.
     *
     * @param array $fields Линейный массив имен полей таблицы
     *
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->_fields = $fields;
        return $this;
    }

    /**
     * Установить имя GET-параметра с направлением текущей сортировки
     *
     * @param string $name
     *
     * @return $this
     */
    public function setOrderParamName($name)
    {
        $this->_orderParamName = $name;
        return $this;
    }

    public function setInput(Data_Transport $input)
    {
        $this->_input = $input;

        return $this;
    }
}