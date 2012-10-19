<?php

/**
 * @desc Тип ссылки через Model_Component
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_Component extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$model = Model_Manager::byKey ($model_name, $id);
		if ($model)
		{
			$collection = $model->component (
				substr ($this->getModel (), strlen ('Component_'))
			);
			return $this->resource ()
				->setItems ($collection->items ());
		}
	}
}