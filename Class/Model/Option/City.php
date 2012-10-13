<?php

/**
 * @desc Опшин для города
 */
class Model_Option_City extends Model_Option
{
	public function before ()
	{
		if (!isset ($this->params ['id']))
		{
			return;
		}
		$city_id = $this->params ['id'];
		$model_name = isset ($this->params ['model'])
			? $this->params ['model']
			: $this->collection->modelName ();
		$this->query->where ($model_name . '.City__id', $city_id);
	}
}