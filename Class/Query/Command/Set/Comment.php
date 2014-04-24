<?php

/**
 * Часть запроса "setComment"
 * 
 * @author morph
 */
class Query_Command_Set_Comment extends Query_Command_Abstract
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
        $setComment = reset($data);
        $this->data = array(Query::COMMENT => $setComment);
    }
}