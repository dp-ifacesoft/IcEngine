<?php

/**
 * по имени
 * @author markov
 */
class Query_Part_Name extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.name',
			$this->params['value']);
	}
}