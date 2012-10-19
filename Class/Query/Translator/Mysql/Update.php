<?php

/**
 * @desc Транслятор запроса типа update драйвера mysql
 * @author morph, goorus
 */
class Query_Translator_Mysql_Update extends Query_Translator_Mysql_Select
{
	/**
	 * @desc Рендеринг UPDATE запроса.
	 * @param Query_Abstract $query Запрос.
	 * @return Сформированный SQL запрос.
	 */
	public function _renderUpdate (Query_Abstract $query)
	{
		$table = $query->part (Query::UPDATE);
		$sql =
			'UPDATE ' .
			strtolower (Model_Scheme::table ($table)) .
			' SET ';

		$values = $query->part (Query::VALUES);
		$sets = array();

		foreach ($values as $field => $value)
		{
			if (
				strpos ($field, '?') !== false ||
				strpos ($field, '=') !== false
			)
			{
				$sets [] = str_replace ('?', $this->_quote ($value), $field);
			}
			else
			{
				$sets [] = self::_escape ($field) . '=' . $this->_quote ($value);
			}
		}

		return $sql . implode (', ', $sets) . ' ' . $this->_renderWhere ($query);
	}
}