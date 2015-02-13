<?php

/**
 * Часть запроса "dropTable"
 * 
 * @author morph
 */
class Query_Command_Drop_Table extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::DROP_TABLE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = array(self::NAME => reset($data));
		return $this;
    }
}