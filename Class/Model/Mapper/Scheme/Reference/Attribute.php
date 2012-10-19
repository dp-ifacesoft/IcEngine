<?php

/**
 * @desc Тип ссылки через Attribute_Manager
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_Attribute extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$model = Model_Manager::byKey ($model_name, $id);
		return $this->resource ()->setItems (array (
			$model->attr ($this->getField ())
		));
	}
}