<?php

/**
 * Часть запроса "addField"
 *
 * @author morph
 */
class Query_Command_Add_Field extends Query_Command_Abstract
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
        $field = $data[0];
        $name = $field->getName();
        $addField = array(
            Query::TYPE     => Query::ADD,
            Query::FIELD    => $name,
            Query::ATTR     => $field->getAttrs()
        );
        $this->data = array(Query::FIELD => array($name => $addField));
        return $this;
    }
}