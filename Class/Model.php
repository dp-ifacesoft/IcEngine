<?php

/**
 * Базовая модель для всех сущностей
 *
 * @author goorus, morph
 */
abstract class Model implements ArrayAccess
{
    /**
     * Название мета-поля с данными модели
     */
    const DATA_FIELD = 'data';

    /**
     * Аннотации моделей
     *
     * @var array
     */
    protected static $annotations = array();

    /**
     * Конфигурация модели
     *
     * @var array|Objective
     */
    protected static $config;

    /**
     * Ошибки
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Связанные данные
     *
     * @var array
     */
    protected $data = array();

    /**
     * Данные модели
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Подгруженные объекты
     *
     * @var array
     */
    protected $joints = array();

    /**
     * Флаг показывает, что модель новая и сохранена в текущем выполнение
     *
     * @var boolean
     */
    protected $isNew = false;

    /**
     * Помощник модели
     *
     * @var Helper_Model
     */
    protected static $helper;

    /**
     * Имя первичного ключа
     */
    protected $keyField;

    /**
     * Означает, что модель отложенная для true
     *
     * @var bool
     */
    protected $lazy;

    /**
     * Схема связей модели
     *
     * @var Model_Mapper_Scheme
     */
    protected $modelMapperScheme;

    /**
     * Репозиторий модели
     *
     * @var Model_Repository
     */
    protected $repository;

    /**
     * Схема модели
     *
     * @var array
     */
    protected $scheme;

    /**
     * Локатор сервисов
     *
     * @var Service_Locator
     */
    protected static $serviceLocator;

    /**
     * Обновленные поля
     *
     * @var array
     */
    protected $updatedFields = array();

    /**
     * Вызов метода через репозиторий модели
     *
     * @param string $method
     * @param array $args
     * @throws Exception
     * @return mixed
     */
    public function __call($method, $args)
    {
        $repository = $this->repository();
        if (!method_exists($repository, $method)) {
            throw new Exception(
                'Method "' . $method . '" unexists in repository'
            );
        }
        return call_user_func_array(array($repository, $method), $args);
    }

    /**
     * Создает и возвращает модель
     *
     * @param array $fields Данные модели
     */
    public function __construct(array $fields = array())
    {
        $this->set($fields);
        $selfFields = $this->helper()->getVars($this);
        foreach (array_keys($selfFields) as $fieldName) {
            if (!$fieldName || $fieldName[0] == '_') {
                continue;
            }
            if (!array_key_exists($fieldName, $this->fields)) {
                $this->fields[$fieldName] = $this->$fieldName;
            }
            unset($this->$fieldName);
        }
    }

    /**
     * Возвращает поле
     *
     * @param string $field Поле
     * @return mixed
     */
    public function __get($field)
    {
        if (is_null($this->fields)) {
            $this->load();
        }
        if ($field == self::DATA_FIELD) {
            return $this->getData();
        }
        $joinField = $field . '__id';
        $references = $this->scheme()->references;
        if (isset($this->joints[$field])) {
            return $this->joints[$field];
        } elseif (isset($references[$field])) {
            if (!$this->modelMapperScheme) {
                $this->modelMapperScheme = $this->getService('modelMapper')
                    ->scheme($this);
            }
            return $this->modelMapperScheme->get($field);
        } elseif (array_key_exists($field, $this->fields)) {
            $field = $this->helper()->unserializeValue(
                $this, $field, $this->fields[$field]
            );
            return $field;
        } elseif (array_key_exists($joinField, $this->fields)) {
            return $this->joint($field, $this->fields[$joinField]);
        }
        $value = null;
        return $value;
    }

    /**
     * Проверяет существует ли поле
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->fields[$key]);
    }

    /**
     * Изменяет значение поля
     *
     * @param string $field Поле
     * @param mixed $value Значение
     * @throws Exception
     * @return mixed
     */
    public function __set($field, $value)
    {
        if (!$this->fields) {
            $this->load();
        }
        if ($field == self::DATA_FIELD) {
            return $this->data($value);
        }
        $fields = $this->scheme()->fields;
        if (isset($fields[$field])) {
            $this->set($field, $value);
        } else {
            throw new Exception(
                'Field or property unexists "' . $field . '" ' . $this->table()
            );
        }
    }

    /**
     * Преобразование к массиву
     *
     * @return array
     */
    public function __toArray()
    {
        return array(
            'class' => get_class($this),
            'model' => $this->modelName(),
            'fields' => $this->asRow(),
            'data' => $this->getData()->__toArray()
        );
    }

    /**
     * Возвращает массив, создержащий все поля модели
     *
     * @return array
     */
    public function asRow()
    {
        return $this->fields ? : array();
    }

    /**
     * Возвращает или устанавливает значение атрибута
     *
     * @param string|array $key Название атрибута или массив пар
     * (название => значение).
     * @param mixed $value [optional] Новое значение атрибута.
     * @return mixed Если не задан второй параметр, возвращает значение
     * аттрибута, иначе null.
     */
    public function attr($key, $value = null)
    {
        $attributeManager = $this->getService('attributeManager');
        if (func_num_args() == 1) {
            if (is_scalar($key)) {
                return $attributeManager->get($this, $key);
            }
        } else {
            $key = array($key => $value);
        }
        $attributeManager->set($this, $key, null);
    }

    /**
     * Для переопределения возвращает базовую модель
     *
     * @return string
     */
    public function baseTable()
    {
        return $this->table();
    }

    /**
     * Имя класса модели
     *
     * @return string
     */
    public function className()
    {
        return get_class($this);
    }

    /**
     * Говорит, новая ли модель
     *
     * @return boolean
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * Присоединить сущность
     *
     * @param string $modelName
     * @param mixed $key
     * @return Model Присоединенная модель
     */
    protected function joint($modelName, $key = null)
    {
        if (!is_null($key)) {
            $modelManager = $this->getService('modelManager');
            $joinedModel = $modelName::getModel($key);
            $this->joints[$modelName] = $joinedModel;
        }
        return $this->joints[$modelName];
    }

    /**
     * Проинициализировать и получить помощник модели
     *
     * @return Model_Helper
     */
    protected function helper()
    {
        if (is_null(self::$helper)) {
            self::$helper = new Helper_Model;
        }
        return self::$helper;
    }

    /**
     * Возвращает коллекцию связанных компонентов или
     * элемент коллекции с указанным индексом
     *
     * @param string $type Тип компонентов
     * @param integer $index Индекс для получения
     * @return Model_Collection
     */
    public function component($type, $index = null)
    {
        $collectionManager = $this->getService('collectionManager');
        $collection = $collectionManager->create('Component_' . $type)
            ->addOptions(
                array(
                    'name' => '::Table',
                    'table' => $this->table()
                ),
                array(
                    'name' => '::Row',
                    'id' => $this->key()
                )
            );
        return !is_null($index) ? $collection->item($index) : $collection;
    }

    /**
     * Загружает и возвращает конфиг для модели
     *
     * @return Objective
     */
    public function config()
    {
        if (!is_object(static::$config)) {
            $configManager = $this->getService('configManager');
            static::$config = $configManager->get(
                get_class($this), static::$config
            );
        }
        return static::$config;
    }

    /**
     * Устанавливает или получает связанные данные объекта
     *
     * @param string $key [optional] Ключ
     * @param mixed $value [optional]
     *        Значение (не обязательно)
     * @return mixed
     *        Текущее значение
     */
    public function data($key = null, $value = null)
    {
        $numArgs = func_num_args();
        if (!$numArgs) {
            return $this->data;
        }

        if ($numArgs == 1) {
            if (is_object($key)) {
                return;
            }

            if (is_array($key)) {
                $data = is_object($this->data()) ? $this->data()->__toArray() : $this->data();
                $this->data = array_merge($data, $key);
            } else {
                return isset($this->data[$key])
                    ? $this->data[$key]
                    : null;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Удаление модели
     */
    public function delete()
    {
        $modelManager = $this->getService('modelManager');
        if ($this->key()) {
            $modelManager->remove($this);
        }
    }

    /**
     * Возвращает коллекцию моделей типа $model,
     * связанных по первичному ключу с этой моделью
     * В модели $model должно существовать поле "THISMODEL__id",
     * где THISMODEL - название этой модели.
     *
     * @param string $modelName
     * @param integer $index
     * @return Model_Collection
     */
    public function external($modelName, $index = null)
    {
        $collectionManager = $this->getService('collectionManager');
        $collection = $collectionManager->create($modelName)
            ->addOptions(array(
                'name' => '::External',
                'model' => $this->modelName(),
                'id' => $this->key()
            ));
        return !is_null($index) ? $collection->item($index) : $collection;
    }

    /**
     * Получение или установка значения
     *
     * @param string $key Поле
     * @internal param mixed $value Значение (не обязательно).
     * Если указано значение, оно будет записано в поле.
     * @return mixed Если $value не передан, будет возвращено значение поля.
     */
    public function field($key)
    {
        if (func_num_args() > 1) {
            $this->__set($key, func_get_arg(1));
        } else {
            return $this->__get($key);
        }
    }

    /**
     * Получить аннотации модели
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->getService('helperAnnotation')
            ->getAnnotation($this->modelName())->getData();
    }

    /**
     * Получить ошбики
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Получить все хранимые данные модели
     *
     * @return array
     */
    public function &getData()
    {
        if (!is_object($this->data)) {
            $this->data = new Objective($this->data);
        }
        return $this->data;
    }

    /**
     * Получить значения полей. Синоним asRow
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Получить помощника модели
     *
     * @return Model_Helper
     */
    public function getHelper()
    {
        return self::$helper;
    }

    /**
     * Отложена ли модель для загрузки
     *
     * @return boolean
     */
    public function getLazy()
    {
        return $this->lazy;
    }

    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (!self::$serviceLocator) {
            self::$serviceLocator = IcEngine::serviceLocator();
        }
        return self::$serviceLocator->getService($serviceName);
    }

    /**
     * Получить текущий сервис локатор
     *
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    /**
     * Получить список обновленных полей
     *
     * @return array
     */
    public function getUpdatedFields()
    {
        return $this->updatedFields;
    }

    /**
     * Проверяет существование поля в модели
     *
     * @param $field
     * @return boolean
     */
    public function hasField($field)
    {
        if (is_null($this->fields)) {
            $this->load();
        }
        return array_key_exists($field, $this->fields);
    }

    /**
     * Возвращает значение первичного ключа
     *
     * @return string|null
     */
    public function key()
    {
        $keyField = $this->keyField();
        return isset($this->fields[$keyField])
            ? $this->fields[$keyField] : null;
    }

    /**
     * Имя поля первичного ключа
     *
     * @return string
     */
    public function keyField()
    {
        if (!$this->keyField) {
            $modelScheme = $this->getService('modelScheme');
            $this->keyField = $modelScheme->keyField($this->table());
        }
        return $this->keyField;
    }

    /**
     * Имя класса модели. Синоним table
     *
     * @return string
     */
    public function modelName()
    {
        return $this->table();
    }

    /**
     * Проверяет существование поля
     *
     * @param string $offset Название поля
     * @return boolean true если поле существует
     */
    public function offsetExists($offset)
    {
        return $this->hasField($offset);
    }

    /**
     * @see Model::__get
     */
    public function &offsetGet($offset)
    {
        $value = $this->__get($offset);
        return $value;
    }

    /**
     * @see Model::__set
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * Исключение поля из модели
     *
     * @param string $offset название поля
     */
    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->fields)) {
            unset($this->fields[$offset]);
        }
    }

    /**
     * Получить данные модели массивом
     * @param array $schema
     * @return array
     */
    public function raw($schema = array())
    {
        $result = $this->asRow();
        if ($schema) {
            foreach (array_keys($result) as $fieldName) {
                if (!in_array($fieldName, $schema)) {
                    unset($result[$fieldName]);
                }
            }
        }
        $data = $this->data() ? : array();
        if (is_object($data)) {
            $data = $data->__toArray();
        }
        return array_merge($result, array(
            'data' => $data
        ));
    }

    /**
     * Название ресурса модели. Состоит из название модели и первичного ключа
     *
     * @return string
     */
    public function resourceKey()
    {
        return $this->table() . '__' . $this->key();
    }

    /**
     * Получить или инициализировать репозиторий модели
     *
     * @return Model_Repository
     */
    protected function repository()
    {
        $modelRepositoryManager = $this->getService(
            'modelRepositoryManager'
        );
        return $modelRepositoryManager->get($this);
    }

    /**
     * Сохранение данных модели
     *
     * @param boolean $hardInsert Принудительное сохранение - даже если уже
     * задано значение первичного ключа
     * @return Model
     */
    public function save($hardInsert = false)
    {
        if (!$this->key()) {
            $this->isNew = true;
        }
        $this->getService('modelManager')->set($this, $hardInsert);
        return $this;
    }

    /**
     * Получить схему модели
     *
     * @return Objective
     */
    public function scheme()
    {
        if (!is_null($this->scheme)) {
            return $this->scheme;
        }
        $scheme = $this->getService('modelScheme')->scheme($this->table());
        $scheme['signals'] = $scheme['signals'] ? : array();
        return $scheme;
    }

    /**
     * Установка значений полей без обновления источника.
     * При использовании этого метод не проверяется сущестовование полей
     * у модели. Это позволяет установить поля для создаваемой модели,
     * однако может привести к ошибкам в дальнейшем при сохранении, если
     * были заданы несуществующие поля.
     *
     * @param string|array $field Имя поля или массив пар "поле - значение".
     * @param string $value Значение поля для случае, если первым параметром
     * передано имя.
     */
    public function set($field, $value = null)
    {
        $fields = is_array($field) ? $field : array($field => $value);
        $scheme = $this->scheme();
        $data = array();
        $schemeFields = array();
        if ($scheme->fields) {
            $schemeFields = array_keys($scheme->fields->__toArray());
        }
        $updatedFields = array();
        $helper = $this->helper();
        foreach ($fields as $field => $value) {
            if (!$schemeFields || in_array($field, $schemeFields)) {
                $value = $helper->filterValue($this, $field, $value);
                if (array_key_exists($field, $this->fields) &&
                    !$helper->validateField($this, $field, $value)
                ) {
                    $this->errors[$field] = true;
                } else {
                    $this->fields[$field] = $value;
                    $updatedFields[$field] = $value;
                    if (isset($this->errors[$field])) {
                        unset($this->errors[$field]);
                    }
                }
            } else {
                $data[$field] = $value;
            }
        }
        if ($updatedFields) {
            $oldUpdatedFields = $this->getUpdatedFields();
            $this->setUpdatedFields(
                array_merge($oldUpdatedFields, $updatedFields)
            );
        }
        if ($data) {
            $this->data($data);
        }
    }

    /**
     * Изменить помощник модели
     *
     * @param mixed $helper
     */
    public function setHelper($helper)
    {
        self::$helper = $helper;
    }

    /**
     * Установить флаг отложенной модели, через Unit Of Work
     *
     * @param bool $value
     */
    public function setLazy($value)
    {
        $this->lazy = $value;
    }

    /**
     * Изменить схему модели
     *
     * @param mixed $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * Изменить локатор сервисов
     *
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }

    /**
     * Изменить поля для обновления
     *
     * @param array $fields
     */
    public function setUpdatedFields($fields)
    {
        $this->updatedFields = $fields;
    }

    /**
     * Тихое получение или установка поля
     *
     * @param string $key Название поля.
     * @param mixed $value [optional] Значение поля.
     * @return mixed Текущее значение поля или null.
     */
    public function sfield($key)
    {
        return $this->hasField($key) ? $this->fields[$key] : null;
    }

    /**
     * Таблица БД
     *
     * @return string
     */
    public function table()
    {
        return $this->className();
    }

    /**
     * Возвращает имя сущности
     *
     * @return string
     */
    public function title()
    {
        return $this->hasField('title') ? $this->title : null;
    }

    /**
     * Загрузка данных модели
     *
     * @param mixed $key Первичный ключ.
     * @return Model Эта модель.
     */
    public function load()
    {
        if ($this->lazy) {
            $this->getService('unitOfWork')->load($this);
        } else {
            $modelManager = $this->getService('modelManager');
            $modelManager->get($this->table(), $this->key(), $this);
        }
        if (is_null($this->fields)) {
            $this->fields = array($this->keyField() => null);
        }
        return $this;
    }

    /**
     * Удаляет поле из объекта
     * Используется в Model_Manager для удаления первичного ключа перед
     * вставкой в БД
     *
     * @param string $name Имя поля.
     * @return Model Эта модель.
     */
    public function unsetField($name)
    {
        if ($this->hasField($name)) {
            unset($this->fields[$name]);
        }
        return $this;
    }

    /**
     * Обновление данных модели и полей в БД
     *
     * @param array $data Массив пар (поле => значение)
     * @param boolean $hardUpdate
     * @return Model Эта модель.
     */
    public function update(array $data, $hardUpdate = false)
    {
        if (is_null($this->fields)) {
            $this->load();
        }
        $scheme = $this->scheme();
        $fields = $scheme->fields;
        foreach ($data as $key => $value) {
            if (!isset($fields[$key])) {
                continue;
            }
//            if ($value == $this->field($key)) {
//                continue;
//            }
            $this->updatedFields[$key] = $value;
        }
        if (!$this->updatedFields && $this->key() && !$hardUpdate) {
            return $this;
        }
        $this->set($this->updatedFields);
        $result = $this->save($hardUpdate);
        if (isset($scheme['updateSignal'])) {
            /** @var Event_Manager $eventManager */
            $eventManager = $this->getService('eventManager');
            $signalName = 'update' . str_replace('_', '', $this->modelName());
            /** @var Event_Signal $signal */
            $signal = $eventManager->getSignal($signalName);
            if ($signal) {
                $signal->setData($this->getFields());
                $signal->notify();
            }
        }
        return $result;
    }

    /**
     * Обертка для получения модели по ключу
     *
     * @param $key
     * @return Model
     */
    public static function getModel($key)
    {
        return IcEngine::getServiceLocator()
            ->getService('modelManager')
            ->byKey(get_called_class(), $key);
    }

    /**
     * Обертка для создания коллекции моделей
     *
     * @return Model_Collection
     */
    public static function getCollection()
    {
        return IcEngine::getServiceLocator()
            ->getService('collectionManager')
            ->create(get_called_class());
    }
}
