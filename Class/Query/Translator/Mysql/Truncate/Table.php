<?php

/**
 * Транслятор запроса типа truncate table для mysql
 *
 * @author goorus, morph
 */
class Query_Translator_Mysql_Truncate_Table extends
    Query_Translator_Mysql_Alter_Table
{
	const SQL_TRUNCATE_TABLE = 'TRUNCATE TABLE';

	/**
	 * Рендерит часть запроса truncate table
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	public function doRenderTruncateTable(Query_Abstract $query)
	{
        $modelScheme = $this->modelScheme();
		$truncateTable = $query->part(Query::TRUNCATE_TABLE);
        $helper = $this->helper();
        $table = $modelScheme->table($truncateTable[Query::NAME]);
		return self::SQL_TRUNCATE_TABLE . ' ' .
			$helper->escape(strtolower($table));
	}
}
