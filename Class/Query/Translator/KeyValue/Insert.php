<?php

/**
 * Транслятор для запросов типа insert для key-value хранилищ
 * 
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Insert extends Query_Translator_KeyValue_Select
{
	/**
     * Запрос на вставку
     * 
	 * @param Query $query
	 * @return array
	 * 		[0] Массив ключей для перезаписи.
	 * 		[1] Значения ключей.
	 */
	public function doRenderInsert(Query_Abstract $query)
	{
        $values = $query->part(Query::VALUES);
		$keys = $this->compileKeys($query->part(Query::INSERT), $values);
		return array($keys, $values);
	}
}