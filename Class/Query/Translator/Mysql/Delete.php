<?php

/**
 * Транслятор запроса типа delete драйвера mysql
 *
 * @author morph, goorus
 */
class Query_Translator_Mysql_Delete extends Query_Translator_Mysql_Select
{
	/**
	 * Рендерит часть запроса delete
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	public function doRenderDelete(Query_Abstract $query)
	{
		$delete = $query->part(Query::DELETE);
        $from = array_values($query->part(Query::FROM));
        $helper = $this->helper();
		foreach($delete as $key => $table) {
            if ($helper->isEscaped($table)) {
                continue;
            }
            $table = $from[$key][Query::TABLE];
            $delete[$key] = $helper->escape($table);
		}
		$tables = ' ' . implode(self::SQL_COMMA, $delete) . ' ';
		return
			self::SQL_DELETE . $tables .
			$this->renderFrom($query) . ' ' .
			$this->renderWhere($query) . ' ' .
            $this->renderLimitoffset($query);
	}
}