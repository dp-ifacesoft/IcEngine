<?php

/**
 * @desc Рендер для поля DEFAULT CHARSET
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Other_Default_Charset extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract
	 */
	public function render ($entity)
	{
		return ' DEFAULT CHARSET=' . $entity->getValue ();
	}
}