<?php

/**
 * @desc запрос типа show
 * @author morph, goorus
 */
class Query_Show extends Query_Select
{
	/**
	 * @see Query::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::SHOW;
		return $this;
	}

	/**
	 * @desc Часть запроса Show
	 * @param string|array $columns
	 * @return Query
	 */
	public function show ($columns)
	{
		$this->_type = Query::SHOW;
		$this->_parts [Query::SHOW] = $columns;
		return $this;
	}
}