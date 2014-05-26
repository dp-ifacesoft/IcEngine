<?php

/**
 * @desc Рендер для атрибута not null
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Field_Attribute_Not_Null extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract::render
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		return ' NOT NULL';
	}
}