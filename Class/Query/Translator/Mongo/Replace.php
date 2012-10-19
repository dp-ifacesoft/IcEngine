<?php

/**
 * @desc Запрос типа replace для mongodb
 * @author goorus, morph
 */
class Query_Translator_Mongo_Replace extends Query_Translator_Mongo_Select
{
	/**
	 * @desc Рендеринг REPLACE запроса.
	 * @param Query $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function _renderReplace (Query $query)
	{
		$table = $query->part (Query::REPLACE);
		return array (
			'method'		=> 'save',
			'collection'	=> strtolower (Model_Scheme::table ($table)),
			'arg0'			=> $values
		);
	}
}