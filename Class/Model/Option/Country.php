<?php

/**
 * @desc Опшин для страны
 */
class Model_Option_Country extends Model_Option
{
	public function before ()
	{
		if (!isset ($this->params ['id']))
		{
			return;
		}
		$country_id = $this->params ['id'];
		$model_name = isset ($this->params ['model'])
			? $this->params ['model']
			: $this->collection->modelName ();
		$this->query->where ($model_name . '.Country__id', $country_id);
	}
}