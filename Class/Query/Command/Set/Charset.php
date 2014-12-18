<?php

/**
 * Часть запроса "setCharset"
 * 
 * @author morph
 */
class Query_Command_Set_Charset extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::MERGE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::CREATE_TABLE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $setCharset = reset($data);
        $this->data = array(Query::DEFAULT_CHARSET => $setCharset);
    }
}