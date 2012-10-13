<?php

class Model_Mapper_Scheme_Resource_ManyToMany_Link_Add
{
	protected $_resource;

	public function setResource ($resource)
	{
		$this->_resource = $resource;
	}

	/**
	 * @desc Сохраняет
	 * @param type $model1
	 * @param type $model2
	 */
	public function link ($model1, $model2, $reference)
	{
		$table_name = $model1->modelName ();
		if ($model2->modelName () != $reference->getModel ())
		{
			$table_name = $model2->modelName ();
		}
		$model_name = $reference->key ($table_name);
		$query = Query::instance ()
			->select ('*')
			->from ($model_name)
			->where (
				$model1->modelName () . '__id', $model1->key ()
			)
			->where (
				$model2->modelName () . '__id', $model2->key ()
			);
		$exists = DDS::execute ($query)->getResult ()->asValue ();
		if ($exists)
		{
			return;
		}
		if ($model2->modelName () != $reference->getModel ())
		{
			$this->_resource->addItem ($model1);
		}
		else
		{
			$this->_resource->addItem ($model2);
		}
		$query = Query::instance ()
			->insert ($model_name)
			->values (array (
				$model1->modelName () . '__id'	=> $model1->key (),
				$model2->modelName () . '__id'	=> $model2->key ()
			));
		DDS::execute ($query);
	}
}