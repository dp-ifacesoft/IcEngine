<?php

/**
 * @desc Аксессор индексов для схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Accessor_Index extends Model_Mapper_Scheme_Accessor_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Accessor_Abstract::get
	 */
	public function get ($scheme, $entity)
	{
		return $entity->getValue ();
	}
}