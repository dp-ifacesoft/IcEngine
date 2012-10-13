<?php

/**
 * @desc Абстрактный рендер схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @desc Получить имя рендера
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 27);
	}

	/**
	 * @desc Отрендерить элемент схемы
	 * @param Model_Mapper_Scheme_Entity $entity
	 * @return string
	 */
	public function render ($entity)
	{

	}
}