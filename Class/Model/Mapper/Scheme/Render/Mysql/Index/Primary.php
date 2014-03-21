<?php

/**
 * @desc Рендер индекса типа primary
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Index_Primary extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract::render
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		$fields = (array) $entity->getValue ()->getFields ();
		return 'PRIMARY KEY(`' . reset ($fields) . '`)';
	}
}