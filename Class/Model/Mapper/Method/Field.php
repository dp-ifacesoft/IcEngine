<?php

/**
 * @desc Создание схемы поля
 */
class Model_Mapper_Method_Field extends Model_Mapper_Method_Abstract
{
	/**
	 * @see Model_Mapper_Method_Abstract::execute
	 */
	public function execute ()
	{
		$part = Model_Mapper_Scheme_Part::byName ('Field');
		return $part->set (
			$this->_params [0],
			isset ($this->_params [1]) ? $this->_params [1] : array ());
	}
}