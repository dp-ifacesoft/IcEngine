<?php

/**
 * Транслятор запроса типа drop table для mysql
 * 
 * @author goorus, morph
 */
class Query_Translator_Mysql_Drop_Table extends 
    Query_Translator_Mysql_Alter_Table
{
	const SQL_DROP_TABLE = 'DROP TABLE';

	/**
	 * Рендерит часть запроса drop table
	 * 
     * @param Query_Abstract $query
	 * @return string
	 */
	public function doRenderDropTable(Query_Abstract $query)
	{
		$dropTable = $query->part(Query::DROP_TABLE);
        $helper = $this->helper();
        $table = $dropTable[Query::NAME];
        $modelScheme = $this->modelScheme();
		return self::SQL_DROP_TABLE . ' ' .
			$helper->escape(strtolower($modelScheme->table($table)));
	}
}
