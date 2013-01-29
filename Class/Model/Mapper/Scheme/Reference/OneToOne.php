<?php

/**
 * @desc Тип ссылки "один-к-одному"
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_OneToOne extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$item = Model_Manager::byQuery (
			$this->getModel (),
			Query::instance ()
				->where ($this->getModel () . '.' . $this->getField (),
					$id)
		);
		return $this->resource ()
			->setItems (array ($item));
	}
}