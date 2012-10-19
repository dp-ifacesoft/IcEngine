<?php

/**
 * @desc Рендер для поля ENGINE
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Other_Comment extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract
	 */
	public function render ($entity)
	{
		return ' COMMENT \'' . addslashes ($entity->getValue ()) . '\'';
	}
}