<?php

/**
 * Часть запроса "setEngine"
 * 
 * @author morph
 */
class Query_Command_Set_Engine extends Query_Command_Abstract
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
        $setEngine = reset($data);
        $this->data = array(Query::ENGINE => $setEngine);
    }
}