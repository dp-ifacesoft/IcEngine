<?php

/**
 * @desc Запрос типа insert
 * @author morph, goorus
 */
class Query_Insert extends Query_Abstract
{
	/**
	 * @see Query::_defaults
	 */
	public static $_defaults = array (
		Query::VALUES => array ()
	);

	/**
	 * @desc Запрос преобразуется в запрос на вставку
	 * @param string $table
	 * @return Query
	 */
	public function insert ($table)
	{
		$this->_parts [Query::INSERT] = $table;
		$this->_type = Query::INSERT;
		return $this;
	}

	/**
	 * @see Query_Select::getTags()
	 */
	public function getTags ()
	{
		$tags = array ();

		$insert = $this->getPart (Query::INSERT);
		if ($insert)
		{
	   		$tags [] = Model_Scheme::table ($insert);
		}

		return array_unique ($tags);
	}

	/**
	 * @see Query::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::INSERT;
		return $this;
	}

	/**
	 * @desc Установка значений для INSERT/UPDATE
	 * @param array $values
	 * @return Query Этот запрос.
	 */
	public function values (array $values)
	{
		if (isset ($this->_parts [Query::VALUES]))
		{
			$this->_parts [Query::VALUES] = array_merge (
				$this->_parts [Query::VALUES],
				$values
			);
		}
		else
		{
			$this->_parts [Query::VALUES] = $values;
		}
		return $this;
	}
}