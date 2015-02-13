<?php

/**
 * Часть запроса "show"
 * 
 * @author morph
 */
class Query_Command_Show extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::SHOW;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = reset($data);
        return $this;
    }
}