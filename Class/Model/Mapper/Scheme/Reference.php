<?php

/**
 * Фабрика ссылок схемы моделей
 * 
 * @author morph
 * @Service("modelMapperSchemeReference")
 */
class Model_Mapper_Scheme_Reference
{
	/**
	 * Получить ссылку по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Reference_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Reference_' . $name;
		return new $className;
	}
}