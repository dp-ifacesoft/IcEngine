<?php

/**
 * Часть запроса "insert"
 * 
 * @author morph
 */
class Query_Command_Insert extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::INSERT;
    
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = reset($data);
        return $this;
    }
}