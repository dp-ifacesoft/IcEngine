<?php

/**
 * Запрос типа insert для mongodb
 *
 * @author morph, goorus, neon
 */
class Query_Translator_Mongo_Insert extends Query_Translator_Mongo_Select
{
	/**
	 * Рендеринг INSERT запроса.
	 *
	 * @param Query $query Запрос.
	 * @return string Сформированный SQL запрос.
	 */
	public function doRenderInsert(Query_Abstract $query)
	{
		$modelScheme = $this->modelScheme();
		$table = $query->part(Query::INSERT);
		$values = $query->part(Query::VALUES);
		$valuesEscaped = array();
		foreach ($values as $valueName => $value) {
			$valuesEscaped[$valueName] = (string) $value;
		}
		return array(
			self::COLLECTION	=> strtolower($modelScheme->table($table)),
			'a'                 => $valuesEscaped
		);
	}
}