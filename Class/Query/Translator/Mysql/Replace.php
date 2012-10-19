<?php

/**
 * @desc Транслятор запроса типа replace драйвера mysql
 * @author morph, goorus
 */
class Query_Translator_Mysql_Replace extends Query_Translator_Mysql_Select
{
	/**
	 * @desc Рендеринг REPLACE запроса.
	 * @param Query_Abstract $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function _renderReplace (Query_Abstract $query)
	{
		$table = $query->part (Query_Replace::REPLACE);
		$sql = 'REPLACE ' . strtolower (Model_Scheme::table ($table)) . ' (';

		$fields = array_keys ($query->part (Query_Replace::VALUES));
		$values = array_values ($query->part (Query_Replace::VALUES));

		for ($i = 0, $icount = count ($fields); $i < $icount; $i++)
		{
			$fields [$i] = self::_escape ($fields [$i]);
			$values [$i] = self::_quote ($values [$i]);
		}

		$fields = implode (', ', $fields);
		$values = implode (', ', $values);

		return $sql . $fields . ') VALUES (' . $values . ')';
	}
}