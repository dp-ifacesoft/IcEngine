<?php

/**
 * Часть запроса "createTable"
 *
 * @author morph
 */
class Query_Command_Create_Table extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::REPLACE;

    /**
     * @inheritdoc
     */
    protected $part = Query::CREATE_TABLE;

    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = array(Query::NAME => reset($data));
        return $this;
    }
}