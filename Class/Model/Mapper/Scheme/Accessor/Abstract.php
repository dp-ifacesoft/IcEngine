<?php

/**
 * Абстрактная аксессор схемы моделей
 * 
 * @author morph
 * @packege Ice\Orm
 */
abstract class Model_Mapper_Scheme_Accessor_Abstract
{
	/**
	 * Получить значение части схемы
	 * 
     * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param mixed $entity
	 * @return mixed
	 */
	abstract public function get($scheme, $state);

	/**
	 * Получить имя
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Model_Mapper_Scheme_Accessor_'));
	}
}