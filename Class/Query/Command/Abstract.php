<?php

/**
 * Абстрактная часть запроса
 * 
 * @author morph
 */
abstract class Query_Command_Abstract
{
    /**
     * Данные комманды
     * 
     * @var array
     */
    protected $data;
 
    /**
     * Стретегия соединения частей в строителе
     * 
     * @var string
     */
    protected $mergeStrategy = Query::PUSH;
    
    /**
     * Запрос, к которому применяется часть
     * 
     * @var Query_Abstract
     */
    protected $query;
    
    /**
     * Имя часть запроса для строителя запросов
     * 
     * @var string
     */
    protected $part;
    
    /**
     * Пул частей
     * 
     * @var Query_Command_Pool
     */
    protected static $pool;
    
    /**
     * Создать часть запроса
     * 
     * @param array $data
     * @return array
     */
    abstract public function create($data);
    
    /**
     * Освободить часть запроса
     */
    public function free()
    {
        $this->reset();
        $this->pool()->append($this);
    }
    
    /**
     * Получить данные запроса
     * 
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Получить название комманды
     * 
     * @return string
     */
    public function getName()
    {
        return substr(get_class($this), strlen('Query_Command_'));
    }
    
    /**
     * Получить название стратегии соединения частей в строителе
     * 
     * @return string
     */
    public function getMergeStrategy()
    {
        return $this->mergeStrategy;
    }
    
    /**
     * Получить запрос
     * 
     * @return Query_Abstract
     */
    public function getQuery()
    {
        return $this->query;
    }
    
    /**
     * Получить название части запроса для строителя
     * 
     * @return string
     */
    public function getPart()
    {
        return $this->part;
    }
    
    /**
     * Получить пул частей (без инициализации)
     * 
     * @return Query_Command_Pool
     */
    public function getPool()
    {
        return self::$pool;
    }
    
    /**
     * Получить пул частей
     * 
     * @return Query_Command_Pool
     */
    public function pool()
    {
        if (is_null(self::$pool)) {
            $serviceLocator = IcEngine::serviceLocator();
            self::$pool = $serviceLocator->getService('queryCommandPool');
        }
        return self::$pool;
    }
    
    /**
     * Заполнить комманду части запроса
     * 
     * @param Query_Abstract $query
     * @param array $data
     * @return array
     */
    public function process($query, $data)
    {
        $this->query = $query;
        $data = $this->create($data)->getData();
        return array($this->part => $data);
    }
    
    /**
     * Сбросить часть
     */
    public function reset()
    {
        $this->query = null;
        $this->data = null;
    }
    
    /**
     * Изменить данные
     * 
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    
    /**
     * Изменить название стратегии соединения частей в строителе
     * 
     * @param string $mergeStategy
     */
    public function setMergeStrategy($mergeStategy)
    {
        $this->mergeStrategy = $mergeStategy;
    }
    
    /**
     * Изменить запрос
     * 
     * @param Query_Abstract $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }
    
    /**
     * Изменить название части запроса для строителя
     * 
     * @param string $part
     */
    public function setPart($part)
    {
        $this->part = $part;
    }
    
    /**
     * Пул частей запроса
     * 
     * @param Query_Command_Pool $pool
     */
    public function setPool($pool)
    {
        $this->pool = $pool;
    }
}