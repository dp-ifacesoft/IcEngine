<?php

/**
 * Часть запроса "changeField"
 *
 * @author morph
 */
class Query_Command_Change_Field extends Query_Command_Abstract
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
        $newName = isset($data[1]) ? $data[1] : null;
        $name = $field->getName();
        $addField = array(
            Query::TYPE     => Query::CHANGE,
            Query::NAME     => $newName,
            Query::FIELD    => $name,
            Query::ATTR     => $field->getAttrs()
        );
        $this->data = array(Query::FIELD => array($name => $addField));
        return $this;
    }
}