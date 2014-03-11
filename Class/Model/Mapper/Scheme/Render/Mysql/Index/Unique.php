<?php

/**
 * @desc Рендер индекса типа unique
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Index_Unique extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract::render
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		$fields = (array) $entity->getValue ()->getFields ();
		$fields = array_map (
			function ($i)
			{
				return '`' . $i . '`';
			},
			$fields
		);
		return 'UNIQUE KEY `' . $entity->getName () .
			'`(' . implode (',', $fields) . ')';
	}
}