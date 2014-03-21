<?php

/**
 * Часть запроса для поля active
 *
 * @author morph
 */
class Query_Part_Active extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.active', 1);
	}
}