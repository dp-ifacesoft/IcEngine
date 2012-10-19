<?php

/**
 * @desc Запрос типа delete для mongodb
 * @author goorus, morph
 */
class Query_Translator_Mongo_Delete extends Query_Translator_Mongo_Select
{
	/**
	 * @desc Формирует запрос на удаление
	 * @param Query $query
	 * @return array
	 */
	public function _renderDelete (Query $query)
	{
		return array (
			'collection'	=> self::_getFromCollection ($query),
			'criteria'		=> self::_getCriteria ($query),
			'options'		=> array ('justOne'	=> false)
		);
	}
}