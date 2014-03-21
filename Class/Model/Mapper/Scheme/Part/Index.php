<?php

/**
 * @desc Часть схемы моделей, отвечающая за индексы
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Part_Index extends Model_Mapper_Scheme_Part_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Part_Abstract::$_specification
	 */
	protected static $_specification = 'indexes';

	/**
	 * @desc Создать индекс схемы модели
	 * @param string $name Название поля
	 * @param mixed fields Атрибуты
	 * @return Model_Mapper_Scheme_Index_Abstract
	 */
	public static function set ($name, $fields)
	{
		$index = Model_Mapper_Scheme_Index::byName ($name);
		$index->setFields ((array) $fields);
		return $index;
	}

	/**
	 * @see Model_Mapper_Scheme_Part_Abstract::execute
	 */
	public function execute ($scheme, $values)
	{
		foreach ($values as $name => $params)
		{
			$fields = array ();
			if (!empty ($params [1]))
			{
				$fields = $params [1];
				$fields = $fields ? $fields->__toArray () : array ();
			}
			$index_name = $name . '_index';
			$scheme->$index_name = self::set (
				$params [0],
				$fields
			);
		}
		return $scheme;
	}
}