<?php

/**
 * Транслятор запросов типа delete для key-value хранилищ
 *
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Delete extends Query_Translator_KeyValue_Select
{
	/**
	 * Возвращает массив масок для удаления ключей.
	 *
	 * @param Query_Abstract $query
	 * @return array Массив ключей к удалению.
	 */
	public function doRenderDelete(Query_Abstract $query)
	{
        $table = $this->extractTable($query);
		return $this->compileKeyMask($table, $query->part(Query::WHERE));
	}
}