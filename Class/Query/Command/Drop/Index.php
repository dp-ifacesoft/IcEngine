<?php

/**
 * Часть запроса "dropIndex"
 *
 * @author morph
 */
class Query_Command_Drop_Index extends Query_Command_Abstract
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
        $index = $data[0];
        $data = array(
            Query::NAME => $index->getName(),
            Query::TYPE => $index->getType(),
            Query::TYPE => Query::DROP
        );
        $this->data = array(Query::INDEX => $data);
        return $this;
    }
}