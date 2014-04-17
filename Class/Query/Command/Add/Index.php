<?php

/**
 * Часть запроса "addIndex"
 *
 * @author morph
 */
class Query_Command_Add_Index extends Query_Command_Abstract
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
        $index = reset($data);
        $name = $index->getName();
        $addIndex = array(
            Query::NAME     => $name,
            Query::TYPE     => $index->getType(),
            Query::FIELD    => $index->getFields()
        );
        $this->data = array(Query::INDEX => array($name => $addIndex));
        return $this;
    }
}