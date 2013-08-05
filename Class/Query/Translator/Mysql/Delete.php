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
        $modelScheme = $this->modelScheme();
        $helper = $this->helper();
		foreach($delete as $key => $table) {
            if ($helper->isEscaped($table)) {
                continue;
            }
            $delete[$key] = $helper->escape(
                strtolower($modelScheme->table($table))
            );
		}
		$tables = count($delete) > 1
            ? ' '. implode(self::SQL_COMMA, $delete) . ' ' : ' ';
		return
			self::SQL_DELETE . $tables .
			$this->renderFrom($query, false) . ' ' .
			$this->renderWhere($query) . ' ' .
            $this->renderLimitoffset($query);
	}
}