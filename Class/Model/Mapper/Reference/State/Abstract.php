<?php

/**
 * Абстрактный объект состояния связи моделей 
 * 
 * @author morph
 * @ServiceAccessor
 */
abstract class Model_Mapper_Reference_State_Abstract
{
    /**
     * Полученная коллекция
     * 
     * @var Model_Collection
     * @Generator
     */
    protected $collection;
    
    /**
     * Количество элементов для выборки
     * 
     * @var integer 
     */
    protected $count = 0;
    
    /**
     * Dto
     * 
     * @var Dto
     */
    protected $dto;
    
    /**
     * Фильтры, которые применяются к целевой коллекции
     * 
     * @var array
     */
    protected $filters = array();
    
    /**
     * Модель
     * 
     * @var Model
     * @Generator
     */
    protected $model;
    
    /**
     * Смещения предела выборки
     * 
     * @var integer 
     */
    protected $offset = 0;

    /**
     * Сортировки
     * 
     * @var array
     */
    protected $orders = array();
    
    /**
     * @see StdClass::__call
     * 
     * @param string $method
     * @param array $args
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function __call($method, $args)
    {
        if (strpos($method, 'by') === 0) {
            $field = lcfirst(substr($method, 2));
            $this->filters[$field] = reset($args);
        }
        return $this;
    }
    
    /**
     * Конструктор
     * 
     * @param Model $model
     * @param Dto $dto
     */
    public function __construct($model, $dto)
    {
        $this->model = $model;
        $this->dto = $dto;
    }
    
    /**
     * Добавить часть запроса
     * 
     * @param mixed $parts
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function addPart($parts)
    {
        if (!$this->collection) {
            $this->load();
        }
        $modelName = $this->model->modelName();
        foreach (func_get_args() as $part) {
            $partName = $part;
            $params = array();
            if (is_array($part)) {
                $partName = reset($part);
                $params = $part[1];
            }
            $className = 'Query_Part_' . $partName;
            $queryPart = new $className($modelName, $params);
			$queryPart->inject($this->collection->query());
        }
        return $this;
    }
    
    /**
     * Добавить опшин к коллекции
     * 
     * @param mixed $options
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function addOptions($options)
    {
        if (!$this->collection) {
            $this->load();
        }
        foreach (func_get_args() as $option) {
            $this->collection->addOptions($option);
        }
        return $this;
    }
    
    /**
     * Получить всю коллекцию
     * 
     * @return Model_Collection
     */
    public function all()
    {
        if (!$this->collection) {
            $this->load();
        }
        return $this->collection;
    }
    
    /**
     * Получает новую коллекциюю
     * 
     * @return mixed
     */
    abstract public function collection();
    
    /**
     * Добавить результат фильтрации к коллекции
     * 
     * @param array $filters
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function filter($filters)
    {
        foreach ($filters as $fieldName => $value) {
            $this->registerField($fieldName, $value);
        }
        return $this;
    }
    
    /**
     * Задает пределы выборки
     * 
     * @param integer $count
     * @param integer $offset
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function limit($count, $offset)
    {
        $this->count = $count;
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * Загружает коллекцию
     */
    public function load()
    {
        if (!$this->collection) {
            $this->collection = $this->collection();
        }
        foreach ($this->filters as $filter => $value) {
            $this->collection->query()->where($filter, $value);
        }
        foreach ($this->orders as $field => $direction) {
            $this->collection->query()->order(array($field => $direction));
        }
        if ($this->count) {
            $this->collection->query()->limit($this->count, $this->offset);
        }
    }
    
    /**
     * Получить следующую часть выборки
     * 
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function next()
    {
        $this->offset += $this->count;
        $this->load();
        return $this;
    }
    
    /**
     * Получить первый элемент коллекции
     * 
     * @return Model
     */
    public function one()
    {
        if (!$this->collection) {
            $this->count = 1;
            $this->load();
        }
        return $this->collection->first();
    }
    
    /**
     * Сортировка
     * 
     * @param array $fields
     * @return Model_Mapper_Reference_State_Abstract
     */
    public function order($fields)
    {
        static $validSortDirections = array(Query::ASC, Query::DESC);
        foreach ($fields as $fieldName => $sortDirection) {
            if (is_numeric($fieldName)) {
                $fieldName = $sortDirection;
                $sortDirection = Query::ASC;
            }
            if (!in_array($sortDirection, $validSortDirections)) {
                $sortDirection = Query::ASC;
            }
            if (!$this->validateField($this->model->modelName(), $fieldName)) {
                continue;
            }
            $this->orders[$fieldName] = $sortDirection;
        }
        return $this;
    }
    
    /**
     * Получить чистые данные из коллекции
     * 
     * @param array $columns
     * @return array
     */
    public function raw($columns = array())
    {
        if (!$this->collection) {
            $this->load();
        }
        return $this->collection->raw($columns);
    }
    
    /**
     * Зарегистрировать фильтр
     * 
     * @param string $field
     * @param mixed $value
     */
    protected function registerField($field, $value)
    {
        if (!$this->validateField($this->model->modelName(), $field)) {
            return null;
        }
        $this->filters[$field] = $value;
    }
    
    /**
     * Проверяет валидность возможности использования поля модели
     * 
     * @param string $modelName
     * @param string $fieldName
     * @return boolean
     */
    protected function validateField($modelName, $fieldName)
    {
        $modelScheme = $this->getService('modelScheme')->scheme($modelName);
        return isset($modelScheme->fields[$fieldName]);
    }
    
    /**
     * Get service by name
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
    
    /**
     * Getter for "model"
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
        
    /**
     * Setter for "model"
     *
     * @param Model model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    
    /**
     * Getter for "collection"
     *
     * @return Model_Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }
        
    /**
     * Setter for "collection"
     *
     * @param Model_Collection collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }
}