<?php

/**
 * @desc Внешняя связь
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_External extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$model = Model_Manager::byKey($model_name, $id);
		$kf = Model_Scheme::keyField($this->getModel());
		$item = Model_Manager::byQuery (
			$this->getModel(),
			Query::instance ()
				->where ($this->getModel() . '.' . $kf,
					$model->sfield($this->getField ()))
		);
		return $this->resource ()
			->setItems (array ($item));
	}
}