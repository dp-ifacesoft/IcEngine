<?php

/**
 * @desc Опшин для курорта
 */
class Model_Option_Resort extends Model_Option
{
	public function before ()
	{
		if (!isset ($this->params ['id']))
		{
			return;
		}
		$resort_id = $this->params ['id'];
		$model_name = isset ($this->params ['model'])
			? $this->params ['model']
			: $this->collection->modelName ();
		$this->query->where ($model_name . '.Resort__id', $resort_id);
	}
}