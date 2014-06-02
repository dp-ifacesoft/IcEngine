<?php

/**
 * @desc Рендер для атрибута auto_increment
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Field_Attribute_Auto_Increment extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract::render
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		return ' AUTO_INCREMENT';
	}
}