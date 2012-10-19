<?php

/**
 * @desc Рендер для атрибута comment
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Field_Attribute_Comment extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract::render
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		$value = $entity->getValue ()->getValue ();
		$value = stripslashes ($value);
		return " COMMENT '" . addslashes ($value) . "'";
	}
}