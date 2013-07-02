<?php

/**
 * Фильтр запросов для группировки в сессию Unit of Work
 * 
 * @author morph
 */
class Data_Filter_Query_Pool extends Data_Filter_Abstract
{
    /**
     * Добавленные запросы
     * 
     * @var array
     * @Generator
     */
    protected $queries = array();
    
    /**
     * @inheritdoc
     */
    public function filter($query, $value = null)
    {
        $modelName = $query->tableName();
        $type = $query->type();
        echo $type . ' ' . $query->translate('Mysql') . PHP_EOL;
        if (!isset($this->queries[$modelName]) || $type == Query::SELECT) {
            return $query;
        }
        if (!isset($this->queries[$modelName][$type])) {
            $this->queries[$modelName][$type] = array();
        }
        $this->queries[$modelName][$type][] = $query;
        return null;
    }
    
    /**
     * Getter for "queries"
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }
        
    /**
     * Добавить модель
     * 
     * @param string $modelName
     */
    public function register($modelName)
    {
        if (!isset($this->queries[$modelName])) {
            $this->queries[$modelName] = array();
        }
    }
    
    /**
     * Setter for "queries"
     *
     * @param array queries
     */
    public function setQueries($queries)
    {
        $this->queries = $queries;
    }
    
    /**
     * Удалить модель
     * 
     * @param string $modelName
     */
    public function unregister($modelName) 
    {
        if (isset($this->queries[$modelName])) {
            unset($this->queries[$modelName]);
        }
    }
}