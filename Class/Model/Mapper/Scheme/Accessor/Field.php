<?php

/**
 * @desc Аксессор полей для схемы моделей
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Accessor_Field extends Model_Mapper_Scheme_Accessor_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Accessor_Abstract::get
	 */
	public function get ($scheme, $entity)
	{
		return $scheme->getModel ()->field ($entity->getName ());
	}
}