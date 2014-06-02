<?php

/**
 * @desc Транслятор запроса типа drop table для mysql
 * @author goorus, morph
 */
class Query_Translator_Mysql_Drop_Table extends Query_Translator_Mysql_Alter_Table
{
	const SQL_DROP_TABLE = 'DROP TABLE';

	/**
	 * @desc Рендерит часть запроса drop table
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderDropTable (Query_Abstract $query)
	{
		$parts = $query->parts ();
		return self::SQL_DROP_TABLE . ' ' .
			$this->_escape ($parts [Query_Drop_Table::DROP_TABLE]
				[Query_Drop_Table::NAME]);
	}
}
