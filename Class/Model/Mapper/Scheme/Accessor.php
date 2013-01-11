<?php

/**
 * Аксессор для частей схемы
 * 
 * @author morph
 * @Service("modelMapperSchemeAccessor")
 */
class Model_Mapper_Scheme_Accessor
{
	/**
	 * Получить часть схемы по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Part_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Accessor_' . $name;
		return new $className;
	}
    
	/**
	 * Получить аксессор части схемы модели по этой части
	 * 
     * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param mixed $entity
	 * @return Model_Mapper_Scheme_Accessor_Abstract
	 */
	public function getAuto($scheme, $entity)
	{
		$accessor = $this->byName($entity->getValue()->factoryName());
		return $accessor->get($scheme, $entity);
	}
}