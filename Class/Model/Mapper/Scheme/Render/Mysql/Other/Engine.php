<?php

/**
 * @desc Рендер для поля ENGINE
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Other_Engine extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract
	 */
	public function render ($entity)
	{
		return ' ENGINE=' . $entity->getValue ();
	}
}