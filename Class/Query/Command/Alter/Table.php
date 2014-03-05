<?php

/**
 * Часть запроса "alterTable"
 * 
 * @author morph
 */
class Query_Command_Alter_Table extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::ALTER_TABLE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = array(Query::NAME => reset($data));
        return $this;
    }
}