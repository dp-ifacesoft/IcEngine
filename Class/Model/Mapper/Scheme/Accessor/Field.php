<?php

/**
 * Аксессор полей для схемы моделей
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Accessor_Field extends 
    Model_Mapper_Scheme_Accessor_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Accessor_Abstract::get
	 */
	public function get($scheme, $state)
	{
		return $scheme->getModel()->field($state->getName());
	}
}