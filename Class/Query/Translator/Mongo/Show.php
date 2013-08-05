<?php

/**
 * Запрос типа show для mongodb
 * 
 * @author morph, goorus
 */
class Query_Translator_Mongo_Show extends Query_Translator_Mongo_Select
{
	/**
	 * Рендеринг SHOW запроса
	 * 
     * @param Query_Abstract $query
     * @return array
	 */
	public function doRenderShow(Query_Abstract $query)
	{
		$from = $query->part(Query::FROM);
		if (!$from) {
			return null;
		}
		if (count($from) > 1) {
			throw new Exception('Multi from not supported.');
		}
		reset($from);
		$table = key($from);
        $modelScheme = $this->modelScheme();
		return array(
			'show'              => $query->part(Query::SHOW),
            self::COLLECTION	=> strtolower($modelScheme->table($table)),
			'model'             => $table
		);
	}
}