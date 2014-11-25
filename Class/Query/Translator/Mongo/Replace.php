<?php

/**
 * Запрос типа replace для mongodb
 * 
 * @author goorus, morph
 */
class Query_Translator_Mongo_Replace extends Query_Translator_Mongo_Select
{
	/**
	 * Рендеринг REPLACE запроса.
	 * 
     * @param Query_Abstract $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function doRenderReplace(Query_Abstract $query)
	{
        $modelScheme = $this->modelScheme();
		$table = $query->part(Query::REPLACE);
        $values = $query->part(Query::VALUES);
		$valuesEscaped = array();
		foreach ($values as $valueName => $value) {
			$valuesEscaped[$valueName] = (string) $value;
		}
		return array(
			self::METHOD        => 'save',
			self::COLLECTION    => strtolower($modelScheme->table($table)),
			'arg0'              => $valuesEscaped
		);
	}
}