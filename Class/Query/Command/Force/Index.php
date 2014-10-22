<?php

/**
 * Часть запроса "forceIndex"
 * 
 * @author morph
 */
class Query_Command_Force_Index extends Query_Command_Abstract
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
        $this->data = array(reset($data), Query::FORCE_INDEX);
        return $this;
    }
}