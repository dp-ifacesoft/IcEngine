<?php

/**
 * Часть запроса для поля active
 *
 * @author morph, neon
 */
class Query_Part_Active extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        $value = isset($this->params['value']) ? $this->params['value'] : 1;
		$this->query->where($this->modelName . '.active', $value);
	}
}