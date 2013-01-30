<?php

/**
 * @desc Транслятор для запросов типа update для key-value хранилища
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Update extends Query_Translator_KeyValue_Select
{
	/**
	 * Данные для обновления записи
	 * @param Query_Abstract $query
	 * @return array
	 * 		[0] Маски ключей для удаления индексов и существующих записей.
	 * 		[1] Ключи для создания новых записей.
	 * 		[2] Новые значеия полей
	 */
	public function _renderUpdate (Query_Abstract $query)
	{
		return array (
			$this->_compileKeyMask (
				$this->extractTable ($query),
				$query->part (Query::WHERE)
			),
			$this->_compileKeys (
				$query->part (Query::UPDATE),
				$query->part (Query::VALUES)
			),
			$query->part (Query::VALUES)
		);
	}
}