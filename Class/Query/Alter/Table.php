<?php

/**
 * Конструктор для запросов класса "ALTER TABLE"
 * 
 * @author morph
 */
class Query_Alter_Table extends Query_Abstract
{
    /**
     * @inheritdoc
     */
    protected $type = Query::INSERT;
    
	/**
	 * Получить атрибуты поля
	 * 
     * @param Model_Field $field
	 * @return array
	 */
	public function getAttrs(Model_Field $field)
	{
		$result = array();
		foreach ($field->getAttrs() as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}
}