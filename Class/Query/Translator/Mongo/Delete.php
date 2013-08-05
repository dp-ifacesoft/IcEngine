<?php

/**
 * Запрос типа delete для mongodb
 * 
 * @author goorus, morph
 */
class Query_Translator_Mongo_Delete extends Query_Translator_Mongo_Select
{
	/**
	 * Формирует запрос на удаление
	 * 
     * @param Query_Abstract $query
	 * @return array
	 */
	public function doRenderDelete(Query_Abstract $query)
	{
		return array(
			self::COLLECTION	=> $this->getFromCollection($query),
			self::CRITERIA      => $this->getCriteria($query),
			self::OPTIONS       => array('justOne'	=> false)
		);
	}
}