<?php

/**
 * Часть запроса "select"
 * 
 * @author morph
 */
class Query_Command_Select extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::MERGE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::SELECT;
    
    /**
     * Получить часть полей
     * 
     * @param string $table
     * @param array $fields
     */
    public function getPartial($table, $fields)
    {
        foreach ($fields as $name => $aliases) {
            foreach ((array) $aliases as $alias) {
                $realName = is_numeric($name) ? $alias : $name;
                $this->data[$alias] = array($table, $realName);
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        foreach ($data as $columns) {
            if (is_array($columns)) {
                foreach ($columns as $table => $fields) {
                    $this->getPartial($table, (array) $fields);
                }
            } elseif ($columns) {
                for ($i = 0, $count = count($data); $i < $count; $i++) {
                    $this->data[$data[$i]] = $data[$i];
                }
            }
        }
        return $this;
    }
}