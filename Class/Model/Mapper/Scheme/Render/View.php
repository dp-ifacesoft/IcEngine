<?php

/**
 * @desc Фабрика представлений рендера схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_View
{
	/**
	 * @desc Получить представление рендера схемы связей модели по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Render_View_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Render_View_' . $name;
		if (!Loader::load ($class_name))
		{
			throw new Model_Mapper_Scheme_Render_View_Exception (
				'Render view had not found'
			);
		}
		return new $class_name;
	}
}