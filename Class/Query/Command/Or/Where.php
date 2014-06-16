<?php

/**
 * Часть запроса "orWhere"
 * 
 * @author morph
 */
class Query_Command_Or_Where extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::WHERE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $where = array(
			0            => Query::SQL_OR,
			Query::WHERE => reset($data)
		);
		if (count($data) > 1) {
			$where[Query::VALUE] = $data[1];
		}
		$this->data = $where;
        return $this;
    }
}