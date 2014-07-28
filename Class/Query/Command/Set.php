<?php

/**
 * Часть запроса "set"
 * 
 * @author morph
 */
class Query_Command_Set extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::MERGE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::VALUES;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = array($data[0] => $data[1]);
        return $this;
    }
}