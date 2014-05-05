<?php

/**
 * @desc Транслятор для запросов типа insert для key-value хранилищ
 * @author goorus, morph
 */
class Query_Translator_KeyValue_Insert extends Query_Translator_KeyValue_Select
{
	/**
	 * @param Query $query
	 * @return array
	 * 		[0] Массив ключей для перезаписи.
	 * 		[1] Значения ключей.
	 */
	public function _renderInsert (Query_Abstract $query)
	{
		$keys = $this->_compileKeys (
			$query->part (Query::INSERT),
			$query->part (Query::VALUES)
		);

		return array (
			$keys,
			$query->part (Query::VALUES)
		);
	}
}