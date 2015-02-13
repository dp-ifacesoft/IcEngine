<?php

/**
 * Часть запроса "useIndex"
 * 
 * @author morph
 */
class Query_Command_Use_Index extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::INDEXES;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = array(reset($data), Query::USE_INDEX);
        return $this;
    }
}