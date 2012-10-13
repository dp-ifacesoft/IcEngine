<?php

/**
 * @desc Аксессор для частей схемы
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Accessor
{
	/**
	 * @desc Получить часть схемы по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Part_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_Accessor_' . $name;
		if (!Loader::load ($class_name))
		{
			echo 1;
			throw new Model_Mapper_Scheme_Accessor_Exception ('Index had not found');
		}
		return new $class_name;
	}
	/**
	 * @desc Получить аксессор части схемы модели по этой части
	 * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param mixed $entity
	 * @return Model_Mapper_Scheme_Accessor_Abstract
	 */
	public static function getAuto ($scheme, $entity)
	{
		$accessor = self::byName ($entity->getValue ()->factoryName ());
		return $accessor->get ($scheme, $entity);
	}
}