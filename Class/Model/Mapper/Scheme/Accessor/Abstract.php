<?php

/**
 * @desc Абстрактная аксессор схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Accessor_Abstract
{
	/**
	 * @desc Получить значение части схемы
	 * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param mixed $entity
	 * @return mixed
	 */
	public function get ($scheme, $entity)
	{

	}

	/**
	 * @desc Получить имя
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 31);
	}
}