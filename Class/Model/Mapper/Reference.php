<?php

/**
 * Фабрика ссылок схемы моделей
 * 
 * @author morph
 * @Service("modelMapperReference")
 */
class Model_Mapper_Reference
{
	/**
	 * Получить ссылку по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Reference_Abstract
	 */
	public function get($name)
	{
		$className = 'Model_Mapper_Reference_' . $name;
		$reference = new $className;
        return $reference;
	}
}