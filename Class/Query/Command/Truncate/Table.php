<?php

/**
 * Часть запроса "truncateTable"
 * 
 * @author morph
 */
class Query_Command_Truncate_Table extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::TRUNCATE_TABLE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = array(Query::NAME => reset($data));
		return $this;
    }
}