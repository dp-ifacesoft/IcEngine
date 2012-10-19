<?php

/**
 * @desc Запрос типа delete
 * @author goorus, morph
 */
class Query_Delete extends Query_Select
{
	/**
	 * @desc Это запрос на удаление
	 * @return Query
	 */
	public function delete ()
	{
		$this->_type = Query::DELETE;
		$this->_parts [Query::DELETE] = func_get_args ();
		return $this;
	}

	/**
	 * @see Query::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::DELETE;
		return $this;
	}
}