<?php

/**
 * @desc Внешняя связь по полю, созданному из значений 2 полей
 * @author neon
 */
class Model_Mapper_Scheme_Reference_ExternalByGlueFields extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
        list($_id, $field1, $glue, $field2) = $this->_field;
        
		$model = Model_Manager::byKey($model_name, $id);
        $result_field = $model->$field1 . $glue . $model->$field2;
        
		$kf = Model_Scheme::keyField($this->getModel());

		$item = Model_Manager::byQuery (
			$this->getModel(),
			Query::instance ()
				->where ($this->getModel() . '.' . $_id,
					$result_field)
		);
        
		return $this->resource ()
			->setItems (array ($item));
	}
}