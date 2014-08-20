<?php

/**
 * Часть запроса "delete"
 * 
 * @author morph
 */
class Query_Command_Delete extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::DELETE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = true;
        return $this;
    }
}