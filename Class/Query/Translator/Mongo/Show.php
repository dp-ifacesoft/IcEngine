<?php

/**
 * @desc Запрос типа show для mongodb
 * @author morph, goorus
 */
class Query_Translator_Mongo_Show extends Query_Translator_Mongo_Select
{
	/**
	 * @desc Рендеринг SHOW запроса
	 * @param Query $query
	 */
	public function _renderShow (Query $query)
	{
		$from = $query->part (Query::FROM);

		if (!$from)
		{
			return;
		}

		if (count ($from) > 1)
		{
			throw new Zend_Exception ('Multi from not supported.');
		}

		//foreach ($from as $alias => $from)


		reset ($from);
		$table = key ($from);
		return array (
			'show'			=> $query->part (Query::SHOW),
			'collection'	=> strtolower (Model_Scheme::table ($table)),
			'model'			=> $table
		);
	}
}