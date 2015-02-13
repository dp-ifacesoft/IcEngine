<?php

/**
 * Часть запроса "dropField"
 *
 * @author morph
 */
class Query_Command_Drop_Field extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::MERGE;

    /**
     * @inheritdoc
     */
    protected $part = Query::ALTER_TABLE;

    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $field = $data[0];
        $data = array(
            Query::FIELD => $field,
            Query::TYPE => Query::DROP
        );
        $this->data = array(Query::FIELD => array($field => $data));
        return $this;
    }
}