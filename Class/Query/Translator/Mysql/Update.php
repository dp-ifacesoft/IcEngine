<?php

/**
 * Транслятор запроса типа update драйвера mysql
 * 
 * @author morph, goorus
 */
class Query_Translator_Mysql_Update extends Query_Translator_Mysql_Select
{
	/**
	 * Рендеринг UPDATE запроса.
	 * 
     * @param Query_Abstract $query Запрос.
	 * @return Сформированный SQL запрос.
	 */
	public function doRenderUpdate(Query_Abstract $query)
	{
        $modelScheme = $this->modelScheme();
		$table = $query->part(Query::UPDATE);
		$sql = self::SQL_UPDATE . ' ' .
			strtolower($modelScheme->table($table)) . ' ' . self::SQL_SET . ' ';
		$values = $query->part(Query::VALUES);
		$sets = array();
        $helper = $this->helper();
		foreach ($values as $field => $value) {
			if (strpos($field, '?') !== false) {
				$sets[] = str_replace('?', $helper->quote($value), $field);
			} else {
				$sets [] = $helper->escape($field) . '=' . 
                    $helper->quote($value);
			}
		}
		return $sql . 
            implode(self::SQL_COMMA, $sets) . ' ' . 
            $this->renderWhere($query);
	}
}