<?php

/**
 * @desc Транслятор запроса типа delete драйвера mysql
 * @author morph, goorus
 */
class Query_Translator_Mysql_Delete extends Query_Translator_Mysql_Select
{
	/**
	 * @desc Рендерит часть запроса delete
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderDelete (Query_Abstract $query)
	{
		$parts = $query->parts ();
		foreach($parts[Query::DELETE] as $key => $part)
		{
			$parts [Query::DELETE][$key] = strpos ($part, self::SQL_ESCAPE)
				!== false
					? $part
					: strtolower (Model_Scheme::table ($part));
			$parts [Query::DELETE][$key] =
				$this->_escape ($parts [Query::DELETE][$key]);
		}
		$tables = count($parts[Query::DELETE]) > 0
			? ' '. implode(', ', $parts [Query::DELETE]) . ' '
			: ' ';

		return
			self::SQL_DELETE . $tables .
			self::_renderFrom ($query, false) . ' ' .
			self::_renderWhere ($query);
	}
}