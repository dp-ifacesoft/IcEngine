<?php

/**
 * Менеджер для загрузчиков от Unit_Of_Work
 *
 * @author neon
 */
class Unit_Of_Work_Loader_Manager
{
	/**
	 * Получить менеджер загрузки
	 *
	 * @param string $name
	 */
	public function get($name = 'Simple')
	{
		$classPrevix = 'Unit_Of_Work_Loader_';
		$className = $classPrevix . $name;
		return new $className();
	}
}