<?php

/**
 * @desc Тип ссылки "один-ко-многим"
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_OneToMany extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$field = $this->getField ();
		if (!$field)
		{
			$field = $model_name . '__' . Model_Scheme::keyField ($model_name);
		}

		$collection = Model_Collection_Manager::byQuery (
			$this->getModel (),
			Query::instance ()
				->where ($this->getModel () . '.' . $field, $id)
		);

		return $this->resource ()
			->setItems ($collection->items ());
	}
}