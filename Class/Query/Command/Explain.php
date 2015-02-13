<?php

/**
 * Часть запроса "explain"
 * 
 * @author morph
 */
class Query_Command_Explain extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::EXPLAIN;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = (bool) reset($data);
        return $this;
    }
}