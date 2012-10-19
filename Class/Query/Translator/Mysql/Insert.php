<?php

/**
 * @desc Транслятор запросов типа insert драйвера mysql
 * @author goorus, morph
 */
class Query_Translator_Mysql_Insert extends Query_Translator_Mysql_Select
{
	/**
	 * @desc Рендеринг INSERT запроса.
	 * @param Query_Abstract $query Запрос.
	 * @return string Сформированный SQL запрос.
	 */
	public function _renderInsert (Query_Abstract $query)
	{
		$table = $query->part (Query::INSERT);
		$sql = 'INSERT ' . strtolower (Model_Scheme::table ($table)) . ' (';

		$fields = array_keys ($query->part (Query::VALUES));
		$values = array_values ($query->part (Query::VALUES));

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