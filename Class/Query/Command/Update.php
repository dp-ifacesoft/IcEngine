<?php

/**
 * Часть запроса "update"
 * 
 * @author morph
 */
class Query_Command_Update extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;

    /**
     * @inheritdoc 
     */
    protected $part = Query::UPDATE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = reset($data);
        return $this;
    }
}