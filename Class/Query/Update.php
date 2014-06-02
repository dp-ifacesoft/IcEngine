<?php

/**
 * @desc Запрос типа update
 * @author morph, goorus
 */
class Query_Update extends Query_Select
{
	/**
	 * @see Query_Select::getTags();
	 */
	public function getTags ()
	{
		$tags = array ();

		$update = $this->getPart (Query::UPDATE);
		if ($update)
		{
	   		$tags [] = Model_Scheme::table ($update);
		}

		return array_unique ($tags);
	}

	/**
	 * @see Query::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::UPDATE;
		return $this;
	}

	/**
	 * @desc Установка значения для UPDATE
	 * @param string $column
	 * @param string $value
	 * @return Query Этот запрос.
	 */
	public function set ($column, $value)
	{
	   	if (isset ($this->_parts [Query::VALUES]))
		{
			$this->_parts [Query::VALUES][$column] = $value;
		}
		else
		{
			$this->_parts [Query::VALUES] = array ($column => $value);
		}
		return $this;
	}

	/**
	 * @desc Преобразует запрос к запросу на обновление.
	 * @param string $table Таблица для обновления.
	 * @return Query Этот запрос.
	 */
	public function update ($table)
	{
		$this->_type = Query::UPDATE;
		$this->_parts [Query::UPDATE] = $table;
		return $this;
	}
}