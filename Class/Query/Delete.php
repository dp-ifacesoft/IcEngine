<?php

/**
 * Запрос типа delete
 *
 * @author goorus, morph
 */
class Query_Delete extends Query_Select
{
    /**
     * @inheritdoc
     */
    protected $type = Query::DELETE;

    /**
     * @inheritdoc
     */
    public function tableName()
    {
        $fromPart = $this->getPart(Query::FROM);
        $keys = array_keys($fromPart);
        $tableName = reset($keys);
        return $tableName;
    }
}