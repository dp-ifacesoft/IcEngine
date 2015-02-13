<?php

/**
 * Транслятор для запросов типа update для key-value хранилища
 * 
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Update extends Query_Translator_KeyValue_Select
{
	/**
	 * Данные для обновления записи
     * 
	 * @param Query_Abstract $query
	 * @return array
	 * 		[0] Маски ключей для удаления индексов и существующих записей.
	 * 		[1] Ключи для создания новых записей.
	 * 		[2] Новые значеия полей
	 */
	public function doRenderUpdate(Query_Abstract $query)
	{
        $values = $query->part(Query::VALUES);
        $table = $this->extractTable($query);
		return array(
			$this->compileKeyMask($table, $query->part(Query::WHERE)),
			$this->compileKeys($query->part(Query::UPDATE), $values),
            $values
		);
	}
}