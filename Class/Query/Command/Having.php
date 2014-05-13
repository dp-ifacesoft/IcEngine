<?php

/**
 * Часть запроса "having"
 * 
 * @author morph
 */
class Query_Command_Having extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::HAVING;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = reset($data);
        return $this;
    }
}