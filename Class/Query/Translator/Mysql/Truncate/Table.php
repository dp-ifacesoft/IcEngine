<?php

/**
 * @desc Транслятор запроса типа truncate table для mysql
 * @author goorus, morph
 */
class Query_Translator_Mysql_Truncate_Table extends Query_Translator_Mysql_Alter_Table
{
	const SQL_TRUNCATE_TABLE = 'TRUNCATE TABLE';

	/**
	 * @desc Рендерит часть запроса truncate table
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderTruncateTable (Query_Abstract $query)
	{
		$parts = $query->parts ();
		$model = $parts [Query_Truncate_Table::TRUNCATE_TABLE]
			[Query_Truncate_Table::NAME];
		return self::SQL_TRUNCATE_TABLE . ' ' .
			$this->_escape (Model_Scheme::table ($model));
	}
}
