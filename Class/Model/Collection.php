<?php

/**
 * Базовый класс коллекции моделей
 *
 * @author goorus, morph, neon
 */
class Model_Collection implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * Функции, которые будут применены после загрузки
     *
     * @var array
     */
    protected $afterLoad = array();

    /**
     * Связанные данные
     *
     * @var array
     */
    protected $data = array();

    /**
     * Помощник коллекции
     *
     * @var Helper_Model_Collection
     */
    protected $helper;

    /**
     * Загружается ли коллекция через метод raw
     *
     * @var boolean
     */
    protected $isRaw = false;

    /**
     * Элементы коллекции
     *
     * @var array
     */
    protected $items;

    /**
     * Итератор коллекции
     *
     * @var Model_Collection_Iterator
     */
    protected $iterator;

    /**
     * Последний выполненный запрос
     *
     * @var Query_Abstract
     */
    protected $lastQuery;

    /**
     * Имя моделей в коллекции
     *
     * @var string
     */
    protected $modelName;

    /**
     * Опции
     *
     * @var Model_Collection_Option_Collection
     */
    protected $options;

    /**
     * Текущий паджинатор
     *
     * @var Paginator
     */
    protected $paginator;

    /**
     * Текущий запрос
     *
     * @var Query_Abstract
     */
    protected $query;

    /**и
     * Результат последнего выполненного запроса
     *
     * @var Query_Result
     */
    protected $queryResult;

    /**
     * Включенные для raw-запроса поля
     *
     * @var array
     */
    protected $rawFields = array();

    /**
     * Локатор сервисов
     *
     * @var Service_Locator
     */
    protected static $serviceLocator;

    /**
     * Преобразование коллекции к массиву
     *
     * @return array
     */
    public function __toArray()
    {
        $result = array(
            'class' => get_class($this),
            'items' => array(),
            'data' => $this->data
        );
        foreach ($this as $item) {
            $result['items'][] = $item->__toArray();
        }
        return $result;
    }

    /**
     * Добавить модель и не только в коллекцию
     *
     * @param Model|Model_Collection|array $item
     * @throws Exception
     * @return Model_Collection
     */
    public function add($item)
    {
        if ($item instanceof Model) {
            $this->items[] = $item;
        } elseif ($item instanceof Model_Collection) {
            foreach ($item as $model) {
                $this->items[] = $model;
            }
        } elseif (is_array($item)) {
            $this->items[] = & $item;
        } else {
            throw new Exception('Model add error');
        }
        return $this;
    }

    /**
     * Добавление нескольких опций к коллекции аналогично
     *
     * @internal param $_
     * @return Model_Collection Эта коллекция
     */
    public function addOptions()
    {
        $options = func_get_args();
        foreach ($options as $option) {
            $this->options[] = $option;
        }
        return $this;
    }

    /**
     * Получить методы для вызова после загрузки коллекции
     *
     * @return array
     */
    public function &afterLoad()
    {
        return $this->afterLoad;
    }

    /**
     * Клонировать модель
     *
     * @param Model_Collection $source
     * @return Model_Collection
     */
    public function assign(Model_Collection $source)
    {
        $this->data($source->data());
        $this->setItems($source->items());
        return $this;
    }

    /**
     * Действия до загрузки коллекции
     */
    protected function beforeLoad()
    {
        $modelScheme = $this->getService('modelScheme');
        $keyField = $this->keyField();
        $query = $this->query();
        $args = func_get_args();
        $modelName = $this->table();
        $schemeConfig = $modelScheme->scheme($modelName);
        $modelFields = array_keys($schemeConfig->fields->asArray());
        $modelFieldsFlipped = array_flip($modelFields);
        if (!$args || (count($args) == 1 && empty($args[0]))) {
            $query->select($modelName . '.*');
            $query->select(array($modelName => $keyField));
        } else {
            foreach ($args as $arg) {
                if (isset($modelFieldsFlipped[$arg])) {
                    $query->select($arg);
                }
            }
            if (!in_array($keyField, $args)) {
                $query->select(array($modelName => $keyField));
            }
        }
        $query->from($modelName);
        if ($this->paginator) {
            $query->calcFoundRows();
            $query->limit(
                $this->paginator->perPage,
                $this->paginator->offset()
            );
        }
        $schemeOptions = $modelScheme->modelOptions($modelName);
        if ($schemeOptions) {
            $this->addOptions($schemeOptions);
        }
        $optionManager = $this->getService('modelOptionManager');
        $optionManager->executeBefore($this, $this->options);
        $this->lastQuery = $query;
    }

    /**
     * Имя базового класса (без суффикса "_Collection")
     *
     * @return string
     */
    public function className()
    {
        return substr(get_class($this), 0, -strlen('_Collection'));
    }

    /**
     * Получить значение поля для всех моделей коллеции
     *
     * @param string|array $name
     * @return array
     */
    public function column($name)
    {
        return $this->getService('helperArray')->column($this->items(), $name);
    }

    /**
     * Количество моделей коллеции
     *
     * @return integer
     */
    public function count()
    {
        return count($this->items());
    }

    /**
     * Получить текущий итератор коллекции
     *
     * @return Model_Collection_Iterator
     */
    public function currentIterator()
    {
        return $this->iterator;
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
        } elseif ($numArgs == 1) {
            if (is_array($key)) {
                $this->data = array_merge($this->data, $key);
            } else {
                return isset($this->data[$key]) ? $this->data[$key] : null;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Удаление всех объектов коллекции
     */
    public function delete()
    {
        if (!is_array($this->items)) {
            $this->load();
        }
        $queryBuilder = $this->getService('query');
        $keyField = $this->keyField();
        $ids = $this->column($keyField);
        $modelName = $this->modelName();
        $query = $queryBuilder
            ->delete()
            ->from($modelName)
            ->where($keyField, $ids);
        $dataSource = $this->getService('modelScheme')->dataSource($modelName);
        $dataSource->execute($query);
        $this->items = array();
    }

    /**
     * Исключает из коллекции элемент с указанным индексом
     *
     * @param integer $index Индекс элемента в списке.
     * @return Model_Collection
     */
    public function exclude($index)
    {
        if (!is_array($this->items)) {
            $this->items();
        }
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
        }
        return $this;
    }

    /**
     * Фильтрация. Возвращает экземпляр новой коллекции
     *
     * @param array $fields
     * @return Model_Collection
     */
    public function &filter($fields)
    {
        if (is_null($fields)) {
            foreach ($fields as $field => $value) {
                if (!ctype_alnum($field[strlen($field) - 1])) {
                    $field .= '?';
                }
                $this->query()->where($field, $value);
            }
            return $this;
        }
        $helperArray = $this->getService('helperArray');
        $collectionManager = $this->getService('collectionManager');
        $modelScheme = $this->getService('modelScheme');
        $modelName = $this->modelName();
        $keyField = $modelScheme->keyField($modelName);
        $collection = $collectionManager->create($modelName)->reset();
        $result = $helperArray->filter($this->items(), $fields);
        if (!$result) {
            return $collection;
        }
        $ids = $helperArray->column($result, $keyField);
        foreach ($ids as $id) {
            foreach ($this->items as &$model) {
                if ($id != $model[$keyField]) {
                    continue;
                }
                $collection->add($model);
            }
        }
        return $collection;
    }

    /**
     * Возвращает первый элемент коллекции
     *
     * @return Model
     */
    public function first()
    {
        $items = & $this->items();
        if ($items) {
            $first = reset($items);
            return $first;
        }
    }

    /**
     * Получить помощник коллекции
     *
     * @return Helper_Model_Collection
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Получить коллекцию опшинов
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Возвращает элементы коллекции, не загружая ее
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        if (!is_array($this->items)) {
            $this->load();
        }
        if ($this->isRaw) {
            return new Model_Collection_Iterator_Array($this);
        }
        return new ArrayIterator($this->items);
    }

    /**
     * Вернуть текущий пагинатор
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Получить сервис
     *
     * @param string $name
     * @return mixed
     */
    public function getService($name)
    {
        if (!self::$serviceLocator) {
            self::$serviceLocator = IcEngine::serviceLocator();
        }
        return self::$serviceLocator->getService($name);
    }

    /**
     * Получить текущий сеовис локатор
     *
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    /**
     * Получить/создать помощник модели
     *
     * @return Helper_Model_Collection
     */
    public function helper()
    {
        if (is_null($this->helper)) {
            $this->helper = new Helper_Model_Collection();
        }
        return $this->helper;
    }

    /**
     * Получить/изменить значение поля isRaw
     *
     * @param boolean $value
     * @return mixed
     */
    public function isRaw($value = null)
    {
        if (is_null($value)) {
            return $this->isRaw;
        }
        $this->isRaw = $value;
    }

    /**
     * Возвращает модель из коллекции
     *
     * @param integer $index Индекс
     * @return Model|null
     */
    public function &item($index)
    {
        $item = null;
        if (!is_array($this->items)) {
            $this->load();
        }
        if ($index < 0) {
            $index += count($this->items);
        }
        if (isset($this->items[$index])) {
            $item = $this->items[$index];
            return $item;
        }
        return $item;
    }

    /**
     * Получить элементы модели
     *
     * @return array
     */
    public function &items()
    {
        if (is_null($this->items)) {
            $this->load();
        }
        return $this->items;
    }

    /**
     * Получить итератор коллекции
     *
     * @return Model_Collection_Iterator
     */
    public function iterator($isFactory = false)
    {
        if (!$this->iterator) {
            $this->iterator = new Model_Collection_Iterator_Single(
                $this, $isFactory
            );
        }
        if (!is_array($this->items)) {
            $this->load();
        }
        return $this->iterator;
    }

    /**
     * Имя ключевого поля
     *
     * @return string
     */
    public function keyField()
    {
        $modelScheme = $this->getService('modelScheme');
        return $modelScheme->keyField($this->modelName());
    }

    /**
     * Получить последнюю модель коллекции
     *
     * @return Model
     */
    public function &last()
    {
        if (!is_array($this->items)) {
            $this->load();
        }
        $last = end($this->items);
        return $last;
    }

    /**
     * Последний выполенный запрос коллекции.
     * Если запрос еще не сформирован, запрос будет сформирован и коллекция
     * будет загружена.
     *
     * @return Query Зарос коллекции.
     */
    public function lastQuery()
    {
        if (!$this->lastQuery) {
            $this->load();
        }
        return $this->lastQuery;
    }

    /**
     * Загрузка данных
     *
     * @param array $columns
     * @return Model_Collection
     */
    public function load($columns = array())
    {
        if (!is_null($this->items)) {
            return $this;
        }
        if ($this->isRaw) {
            $this->raw();
        } else {
            $this->beforeLoad($columns);
            $query = $this->lastQuery;
            $collectionManager = $this->getService('collectionManager');
            $optionManager = $this->getService('modelOptionManager');
            $collectionManager->load($this, $query);
            $optionManager->executeAfter($this, $this->options);
            if ($this->paginator) {
                $this->paginator->total = $this->data['foundRows'];
            }
        }
        return $this;
    }

    /**
     * Для каждого объекта коллекции будет вызвана функция $function
     * и результат выполнения записан в данные объекта под именем $data
     *
     * @param function $function
     * @param string $data
     */
    public function map($function, $data)
    {
        $items = & $this->items();
        foreach ($items as $item) {
            $item->data($data, call_user_func($function, $item));
        }
    }

    /**
     * Название модели (без суффикса "_Collection")
     *
     * @return string
     */
    public function modelName()
    {
        return $this->table();
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     * @internal param $offset
     * @return Model
     */
    public function offsetGet($offset)
    {
        return $this->item($offset);
    }

    /**
     * Возвращает текущий запрос
     *
     * @return Query_Abstract
     */
    public function query()
    {
        if (!$this->query) {
            $this->query = $this->getService('query')->factory('Select');
        }
        return $this->query;
    }

    /**
     * Получить результат запроса коллекции
     *
     * @param null $result
     * @return Query_Result
     */
    public function queryResult($result = null)
    {
        if ($result) {
            $this->queryResult = $result;
        } else {
            if (!$this->queryResult) {
                $this->load();
            }
            return $this->queryResult;
        }
    }

    /**
     * Получить чистые данные
     *
     * @param array $columns
     * @param string $index
     * @return array
     */
    public function raw($columns = array(), $index = null)
    {
        $helperArray = $this->getService('helperArray');
        $keyField = $this->keyField();
        $this->isRaw = true;
        $isNew = false;
        $optionManager = $this->getService('modelOptionManager');
        if (is_null($this->items)) {
            $isNew = true;
            if ($columns) {
                call_user_func_array(array($this, 'beforeLoad'), $columns);
            } else {
                $this->beforeLoad(array());
            }
            $collectionManager = $this->getService('collectionManager');
            $pack = $collectionManager->callDelegee(
                $this, $this->lastQuery
            );

            if ($pack) {
                $addicts = $this->data('addicts');
                if ($addicts) {
                    $this->items = array();
                    foreach ($pack['items'] as $i => $item) {
                        $itemAddicts = $addicts[$i];
                        if (is_array($addicts[$i])) {
                            $item = array_merge($item, $itemAddicts);
                            $this->rawFields = array_keys($itemAddicts);
                        }
                        $this->items[] = $item;
                    }
                } else {
                    $this->items = $pack['items'];
                }
            }
        } elseif (!$this->items) {
            return array();
        } else {
            $items = array();
            foreach ($this->items as $item) {
                if (!isset($item[$keyField])) {
                    $items[] = $item;
                } else {
                    $items[$item[$keyField]] = $item;
                }
            }
            $this->items = $items;
        }
        foreach ($this->items as $key => $data) {
            $this->items[$key]['data'] = new ArrayIterator(
                isset($data['data']) ? (array)$data['data'] : array()
            );
        }
        if ($isNew) {
            $optionManager->executeAfter($this, $this->options);
            if ($this->paginator) {
                $this->paginator->total = $this->data('foundRows');
            }
            if ($this->afterLoad) {
                foreach ($this->afterLoad as $method) {
                    call_user_func($method, $this);
                }
            }
        }
        if (!$columns) {
            $modelScheme = $this->getService('modelScheme');
            $scheme = $modelScheme->scheme($this->modelName());
            if ($scheme->fields) {
                $columns = array_keys($scheme->fields->asArray());
            }
        }
        if (count($columns) == 1) {
            $columnName = reset($columns);
            $keyField = $columnName;
        } elseif ($columns && !in_array($keyField, $columns)) {
            $keyField = reset($columns);
        }
        $result = $helperArray->column($this->items, $columns, $keyField);
        if (count($columns) == 1) {
            foreach ($result as $i => $row) {
                unset($result[$i]);
                $result[$row] = array($columnName => $row);
            }
        }
        foreach ($this->items as $item) {
            if (!is_array($this->items) || !isset($item['data'])) {
                continue;
            }
            if (!isset($result[$item[$keyField]]['data'])) {
                $result[$item[$keyField]]['data'] = array();
            }
            $data = (array)$item['data'];
            foreach (array_keys($data) as $fieldName) {
                if (in_array($fieldName, (array)$this->rawFields)) {
                    unset($data[$fieldName]);
                }
            }
            $result[$item[$keyField]]['data'] = array_merge(
                (array)$result[$item[$keyField]]['data'], $data
            );
        }

        if ($this->rawFields) {
            foreach ($this->items as $item) {
                $subColumns = $helperArray->column(
                    $item, (array)$this->rawFields
                );
                if (empty($subColumns)) {
                    continue;
                }
                if (!isset($result[$item[$keyField]])) {
                    $result[$item[$keyField]] = array();
                }

                foreach ($this->rawFields as $i => $fieldName) {
                    $result[$item[$keyField]][$fieldName] = null;

                    if (!empty($subColumns[0])) {
                        $result[$item[$keyField]][$fieldName] = isset($item[$fieldName])
                        ? $item[$fieldName]
                        : $item['data'][$fieldName];
                    } else {
                        $result[$item[$keyField]][$fieldName] = isset($item['data'][$fieldName])
                        ? $item['data'][$fieldName]
                            :$subColumns[0];
                    }
                  }
            }
            $this->rawFields = array();
        }
        $readyResult = array_values((array)$result);
        if ($index) {
            $readyResult = $helperArray->reindex($readyResult, $index);
        }
        return $readyResult;
    }

    /**
     * Получить список полей для raw-запроса
     *
     * @return array
     */
    public function &rawFields()
    {
        return $this->rawFields;
    }

    /**
     * Очищает коллекцию
     *
     * @return Model_Collection Эта коллекция.
     */
    public function reset()
    {
        $this->items = array();
        return $this;
    }

    /**
     * Сбросить итератор коллекции
     */
    public function resetIterator()
    {
        $this->iterator = null;
    }

    /**
     * Реверсировать последовательность моделей коллекции
     *
     * @return Model_Collection
     */
    public function reverse()
    {
        $this->items = array_reverse($this->items());
        return $this;
    }

    /**
     * Меняет поля модели
     *
     * @internal param mixed $fields
     * @return Model_Collection
     */
    public function set()
    {
        $args = func_get_args();
        if (count($args) == 2) {
            $args = array($args[0] => $args[1]);
        }
        foreach ($this as $item) {
            foreach ((array)$args as $field => $value) {
                $item->field($field, $value);
            }
        }
        return $this;
    }

    /**
     * Изменить помощник коллекции моделей
     *
     * @param Helper_Model_Collection $helper
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
    }

    /**
     * Заменить модели коллекции
     *
     * @param array <Model> $items
     * @return Model_Collection
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Изменить имя моделей коллекции
     *
     * @param string $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    /**
     * Изменить опшины
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Изменить паджинатор коллекции
     *
     * @param Paginator $paginator
     */
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;
        if ($paginator) {
            $this->paginator->total = is_array($this->items)
                ? count($this->items) : 0;

            if ($this->items) {
                $this->slice($paginator->offset(), $paginator->perPage);
            }
        }
    }

    /**
     * Подмена запроса коллекции
     *
     * @param Query_Abstract $query Новый запрос
     * @return Model_Collection Эта коллекция
     */
    public function setQuery(Query_Abstract $query)
    {
        $this->lastQuery = null;
        $this->query = $query;
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
     * Вернуть первый элемент коллекции, удалив его из коллекции
     *
     * @return Model|null
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * Перемешивает элементы коллекции в случайном порядке
     *
     * @return Model_Collection
     */
    public function shuffle()
    {
        $this->items();
        shuffle($this->items);
        return $this;
    }

    /**
     * Оставить часть элементов коллекции
     *
     * @param integer $offset
     * @param integer $length
     * @return Model_Collection Эта коллекция.
     */
    public function slice($offset, $length)
    {
        if (is_null($this->items)) {
            $this->query()->limit($length, $offset);
            return $this;
        }
        $this->items();
        $this->items = array_slice($this->items, $offset, $length);
        return $this;
    }

    /**
     * Сортировка коллекции
     *
     * @internal param string $fields Список полей для сортировки.
     * Например: "id", "id DESC", "id, rating DESC".
     * @return Model_Collection
     */
    public function sort()
    {
        $args = implode(',', func_get_args());
        if (is_null($this->items)) {
            $this->query()->order($args);
            return $this;
        }
        $items = & $this->items();
        $helperArray = $this->getService('helperArray');
        $helperArray->mosort($items, $args);
        return $this;
    }

    /**
     * Имя таблицы по умолчанию для коллекции
     *
     * @return string
     */
    public function table()
    {
        return $this->modelName;
    }

    /**
     * Получить колекцию уникальный элементов
     *
     * @return Model_Collection
     */
    public function unique()
    {
        $modelName = $this->modelName();
        $keyField = $this->keyField();
        $keys = array_unique($this->column($keyField));
        $collectionManager = $this->getService('collectionManager');
        $collection = $collectionManager->create($modelName)
            ->reset();
        $existsKeys = array();
        foreach ($this->items() as $model) {
            foreach ($keys as $key) {
                if ($key == $model->key() && !in_array($key, $existsKeys)) {
                    $collection->add($model);
                    $existsKeys[] = $key;
                }
            }
        }
        return $collection;
    }

    /**
     * Обновление всех элементов коллекции
     *
     * @param array $data
     */
    public function update(array $data)
    {
        $items = & $this->items();
        $queryBuilder = $this->getService('query');
        $unitOfWork = $this->getService('unitOfWork');
        foreach ($items as $item) {
            $query = $queryBuilder
                ->update($item->table())
                ->values($data)
                ->where($item->keyField(), $item->key());
            $unitOfWork->push($query);
        }
        $unitOfWork->flush();
    }
    
    /**
     * Обновляет как модели, чтобы вызывались Event Slot'ы
     * @param array $data данные для апдейта
     */
    public function updateAsModel(array $data) {
        $modelCollection = $this->items();
        foreach($modelCollection as $model){
            if (isset($model) && $model instanceof Model) {
                $model->update($data);
            }
        }
    }
}