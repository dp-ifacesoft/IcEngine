<?php

/**
 * Часть запроса "replace"
 * 
 * @author morph
 */
class Query_Command_Replace extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = reset($data);
        return $this;
    }
}