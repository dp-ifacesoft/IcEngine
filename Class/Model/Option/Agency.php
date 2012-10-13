<?php

/**
 * @desc Опшин для агентства
 */
class Model_Option_Agency extends Model_Option
{
	public function before ()
	{
		if (!isset ($this->params ['id']))
		{
			return;
		}
		$agency_id = $this->params ['id'];
		$model_name = isset ($this->params ['model'])
			? $this->params ['model']
			: $this->collection->modelName ();
		$this->query->where ($model_name . '.Agency__id', $agency_id);
	}
}