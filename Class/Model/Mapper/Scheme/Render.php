<?php

/**
 * Фабрика представлений рендера схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 * @Service("modelMapperSchemeRender")
 */
class Model_Mapper_Scheme_Render
{
	/**
	 * Получить представление рендера схемы связей модели по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Render_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Render_' . $name;
		return new $className;
	}
}