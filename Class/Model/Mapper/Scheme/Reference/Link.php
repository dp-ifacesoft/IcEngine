<?php

/**
 * @desc Тип ссылки через Helper_Link
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_Link extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$model = Model_Manager::byKey ($model_name, $id);
		if ($model)
		{
			$collection = Helper_Link::linkedItems (
				$model, $this->getModel ()
			);
			return $this->resource ()
				->setItems ($collection->items ());
		}
	}
}