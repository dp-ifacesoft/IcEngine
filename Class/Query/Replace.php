<?php

/**
 * @desc Запрос типа replace
 * @author morph, goorus
 */
class Query_Replace extends Query_Select
{
	/**
	 * @see Query_Select::getTags();
	 */
	public function getTags ()
	{
		$tags = array ();

		$replace = $this->getPart (Query::REPLACE);
		if ($replace)
		{
	   		$tags [] = Model_Scheme::table ($replace);
		}

		return array_unique ($tags);
	}

	/**
	 * @see Query::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::REPLACE;
		return $this;
	}

	/**
	 * @desc Запрос преобразуется в запрос на replace.
	 * @param string $table таблица.
	 * @return Query Этот запрос.
	 */
	public function replace ($table)
	{
		$this->_parts [Query::REPLACE] = $table;
		$this->_type = Query::REPLACE;
		return $this;
	}
}