<?php

/**
 * Часть запроса "where"
 * 
 * @author morph
 */
class Query_Command_Where extends Query_Command_Abstract
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
			0		     => Query::SQL_AND,
			Query::WHERE	 => reset($data)
		);
		if (count($data) > 1) {
			$where[Query::VALUE] = $data[1];
		}
		$this->data = $where;
		return $this;
    }
}