<?php

/**
 * Транслятор запроса типа show драйвера mysql
 * 
 * @author morph, goorus
 */
class Query_Translator_Mysql_Show extends Query_Translator_Mysql_Select
{
	/**
	 * Рендер части SHOW.
	 * 
     * @param Query_Abstract $query Запрос.
	 * @return string Сформированный запрос.
	 */
	public function doRenderShow(Query_Abstract $query)
	{
		$sql = self::SQL_SHOW . ' ' . $this->partDistinct($query) . ' ';
		$sql .= $query->part(Query::SHOW);
		return $sql . ' ' .
			$this->renderFrom($query, false) . ' ' .
			$this->renderWhere($query) . ' ' .
			$this->renderOrder($query) . ' ' .
			$this->renderLimitoffset($query) . ' ' .
			$this->renderGroup($query);
	}
}