<?php

/**
 * @desc Транслятор запроса типа select для mongodb
 * @author morph, goorus
 */
class Query_Translator_Mongo_Update extends Query_Translator_Mongo_Select
{
	/**
	 * @desc Рендеринг UPDATE запроса.
	 * @param Query $query Запрос.
	 * @return array
	 */
	public function _renderUpdate (Query $query)
	{
		$table = $query->part (Query::UPDATE);
		return array (
			'collection'	=> strtolower (Model_Scheme::table ($table)),
			'criteria'		=> $this->_getCriteria ($query),
			'newobj'		=> $query->part (Query::VALUES),
			'options'		=> array (
				'upsert'		=> true,
				'multi'			=> $query->part (Query::LIMIT_COUNT) != 1
			)
		);
	}
}