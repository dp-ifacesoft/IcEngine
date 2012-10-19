<?php

/**
 * @desc Транслятор запросов типа delete для key-value хранилищ
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Delete extends Query_Translator_KeyValue_Select
{
	/**
	 * Возвращает массив масок для удаления ключей.
	 * @param Query_Abstract $query
	 * @return array
	 * 		Массив ключей к удалению.
	 */
	public function _renderDelete (Query_Abstract $query)
	{
		return $this->_compileKeyMask (
			$this->extractTable ($query),
			$query->part (Query::WHERE)
		);
	}
}