<?php

/**
 * @desc Транслятор запроса типа show драйвера mysql
 * @author morph, goorus
 */
class Query_Translator_Mysql_Show extends Query_Translator_Mysql_Select
{
	/**
	 * @desc Рендер части SHOW.
	 * @param Query_Abstract $query Запрос.
	 * @return string Сформированный запрос.
	 */
	public function _renderShow (Query_Abstract $query)
	{
		$sql = 'SHOW ' . $this->_partDistinct ($query) . ' ';

		$sql .= $query->part (Query::SHOW);

		return $sql . ' ' .
			self::_renderFrom ($query, false) . ' ' .
			self::_renderWhere ($query) . ' ' .
			self::_renderOrder ($query) . ' ' .
			self::_renderLimitoffset ($query) . ' ' .
			self::_renderGroup ($query);
	}
}