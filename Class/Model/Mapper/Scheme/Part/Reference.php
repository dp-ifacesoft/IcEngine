<?php

/**
 * @desc Часть схемы моделей, отвечающая за ссылки
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Part_Reference extends Model_Mapper_Scheme_Part_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Part_Abstract::$_specification
	 */
	protected static $_specification = 'references';

	/**
	 * @desc Создать ссылку схемы модели
	 * @param string $name Название ссыдки
	 * @param string $model Имя модели
	 * @param string $field имя поля
	 * @return Model_Mapper_Scheme_Reference_Abstract
	 */
	public static function set ($name, $model, $field)
	{
		$reference = Model_Mapper_Scheme_Reference::byName ($name);
		$reference->setModel ($model);
		$reference->setField ($field);

		return $reference;
	}

	/**
	 * @see Model_Mapper_Scheme_Part_Abstract::execute
	 */
	public function execute ($scheme, $values)
	{
		foreach ($values as $name => $params)
		{
			$scheme->$name = self::set (
				$params [0],
				$params [1],
				isset ($params [2]) ? $params [2] : null
			);
		}
		return $scheme;
	}
}