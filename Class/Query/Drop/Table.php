<?php

/**
 * @desc Запрос типа drop table
 * @author morph, goorus
 */
class Query_Drop_Table extends Query_Abstract
{
	const DROP_TABLE = 'DROP TABLE';
	const NAME		 = '__NAME__';

	/**
	 * @see Query_Abstract::$_defaults
	 */
	public static $_defaults = array (
		self::DROP_TABLE => array ()
	);

	/**
	 * @desc Удаляет таблицу... пф
	 * @param string $table
	 * @return Query_Drop_Table
	 */
	public function dropTable ($table)
	{
		$this->_parts [self::DROP_TABLE] = array (
			self::NAME => $table
		);
		return $this;
	}

	/**
	 * @see Query_Abstract::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::DELETE;
		return $this;
	}
}