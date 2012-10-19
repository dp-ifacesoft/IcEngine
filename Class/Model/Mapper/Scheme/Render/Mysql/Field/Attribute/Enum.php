<?php

/**
 * @desc Рендер для атрибута enum
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Field_Attribute_Enum extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract::render
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		$value = $entity->getValue ()->getValue ();

		return '(' . implode (',', array_map (
			function ($a)
			{
				return '\'' . addslashes ($a) . '\'';
			},
			$value
		)) . ')';
	}
}