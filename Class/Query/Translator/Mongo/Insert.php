<?php

/**
 * @desc Запрос типа insert для mongodb
 * @author morph, goorus
 */
class Query_Translator_Mongo_Insert extends Query_Translator_Mongo_Select
{
	/**
	 * @desc Рендеринг INSERT запроса.
	 * @param Query $query Запрос.
	 * @return string Сформированный SQL запрос.
	 */
	public function _renderInsert (Query $query)
	{
		$table = $query->part (Query::INSERT);
		return array (
			'collection'	=> strtolower (Model_Scheme::table ($table)),
			'a'				=> $query->part (Query::VALUES)
		);
	}
}