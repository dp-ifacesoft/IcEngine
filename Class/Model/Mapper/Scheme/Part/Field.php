<?php

/**
 * @desc Часть схемы моделей, отвечающая за поля
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Part_Field extends Model_Mapper_Scheme_Part_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Part_Abstract::$_specification
	 */
	protected static $_specification = 'fields';

	/**
	 * @desc Создать поле схемы модели
	 * @param string $name Название поля
	 * @param array $attributes Атрибуты
	 * @return Model_Mapper_Scheme_Field_Abstract
	 */
	public static function set ($name, array $attributes = array ())
	{
		$field = Model_Mapper_Scheme_Field::byName ($name);
		foreach ($attributes as $name => $value)
		{
			if (is_numeric ($name))
			{
				$name = $value;
				$value = null;
			}
			$attribute = Model_Mapper_Scheme_Field_Attribute::byName ($name);
			$attribute->setValue ($value);
			$field->attributes ()->add ($attribute);
		}
		return $field;
	}

	/**
	 * @see Model_Mapper_Scheme_Part_Abstract::execute
	 */
	public function execute ($scheme, $values)
	{
		foreach ($values as $name => $params)
		{
			$attributes = array ();
			if (!empty ($params [1]))
			{
				$attributes = $params [1];
				$attributes = $attributes ? $attributes->__toArray () : array ();
			}
			$scheme->$name = self::set (
				$params [0],
				$attributes
			);
		}
		return $scheme;
	}
}