<?php

/**
 * @desc Рендер для атрибута default
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Field_Attribute_Default extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract::render
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		$value = $entity->getValue ()->getValue ();
		if (is_null ($value))
		{
			return ' DEFAULT NULL';
		}
		return " DEFAULT '" . addslashes ($value) . "'";
	}
}