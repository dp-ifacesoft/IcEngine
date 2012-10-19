<?php

/**
 * @desc Рендер индекса типа key
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Index_Key extends Model_Mapper_Scheme_Render_Abstract
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
		return 'KEY `' . $entity->getName () .
			'`(' . implode (',', $fields) . ')';
	}
}