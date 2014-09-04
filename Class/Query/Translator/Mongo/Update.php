<?php

/**
 * Транслятор запроса типа select для mongodb
 *
 * @author morph, goorus, neon
 */
class Query_Translator_Mongo_Update extends Query_Translator_Mongo_Select
{
	/**
	 * Рендеринг UPDATE запроса.
	 *
	 * @param Query $query Запрос.
	 * @return array
	 */
	public function doRenderUpdate(Query_Abstract $query)
	{
		$modelScheme = $this->modelScheme();
		$table = $query->part(Query::UPDATE);
		return array(
			self::COLLECTION	=> strtolower($modelScheme->table($table)),
			self::CRITERIA		=> $this->getCriteria($query),
			'newobj'            => $query->part(Query::VALUES),
			self::OPTIONS		=> array(
				'upsert'		=> true,
				'multi'			=> $query->part(Query::LIMIT_COUNT) != 1
			)
		);
	}
}