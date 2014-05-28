<?php

/**
 * Часть запроса "group"
 * 
 * @author morph
 */
class Query_Command_Group extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::GROUP;

    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $columns = reset($data);
        if (!is_array($columns)) {
			$columns = $data;
		}
        $this->data = $columns;
		return $this;
    }
}