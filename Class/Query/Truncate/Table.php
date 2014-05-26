<?php

/**
 * @desc Запрос типа truncate table
 * @author morph, goorus
 */
class Query_Truncate_Table extends Query_Abstract
{
	const TRUNCATE_TABLE = 'TRUNCATE TABLE';
	const NAME			 = '__NAME__';

	/**
	 * @see Query_Abstract::$_defaults
	 */
	public static $_defaults = array (
		self::TRUNCATE_TABLE => array ()
	);

	/**
	 * @desc Очищает таблицу бида... пф
	 * @param string $table
	 * @return Query_Truncate_Table
	 */
	public function truncateTable ($table)
	{
		$this->_parts [self::TRUNCATE_TABLE] = array (
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