<?php

/**
 * Часть запроса "limit"
 * 
 * @author morph
 */
class Query_Command_Limit extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::LIMIT;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = array(
            Query::LIMIT_COUNT  => (int) $data[0],
            Query::LIMIT_OFFSET => !empty($data[1]) ? (int) $data[1] : 0
        );
        return $this;
    }
}