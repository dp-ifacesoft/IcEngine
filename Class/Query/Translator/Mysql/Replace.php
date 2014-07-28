<?php

/**
 * Транслятор запроса типа replace драйвера mysql
 * 
 * @author morph, goorus
 */
class Query_Translator_Mysql_Replace extends Query_Translator_Mysql_Select
{
	/**
	 * Рендеринг REPLACE запроса.
	 * 
     * @param Query_Abstract $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function doRenderReplace(Query_Abstract $query)
	{
        $modelScheme = $this->modelScheme();
		$table = $query->part(Query::REPLACE);
		$sql = self::SQL_REPLACE . ' ' . 
            strtolower($modelScheme->table($table)) . ' (';
		$fields = array_keys($query->part(Query::VALUES));
		$values = array_values($query->part(Query::VALUES));
        $helper = $this->helper();
		for ($i = 0, $count = count($fields); $i < $count; $i++) {
			$fields[$i] = $helper->escape($fields[$i]);
			$values[$i] = $helper->quote($values[$i]);
		}
		$resultFields = implode(self::SQL_COMMA, $fields);
		$resultValues = implode(self::SQL_COMMA, $values);
		return $sql . 
            $resultFields . ') ' . self::SQL_VALUES . ' (' . 
            $resultValues . ')';
	}
}