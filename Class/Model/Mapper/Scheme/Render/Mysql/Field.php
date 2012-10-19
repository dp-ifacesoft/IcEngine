<?php

/**
 * @desc Рендер для полей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Field extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		$result = '`'. $entity->getName () . '` ' .
			strtolower ($entity->getValue ()->getName ());

		$attributes = $entity->getValue ()->attributes ();

		if ($attributes)
		{
			$sub_render = Model_Mapper_Scheme_Render::byArgs (
				'Mysql',
				'Field',
				'Data'
			);
			$sub_entity = new Model_Mapper_Scheme_Entity (
				$entity->getValue ()->getName (),
				null,
				$attributes
			);
			$result = array (
				'result'	=> $result,
				'data'		=> $sub_render->render ($sub_entity)
			);
		}
		else
		{
			$result = array ('result' => $result);
		}

		return $result;
	}
}