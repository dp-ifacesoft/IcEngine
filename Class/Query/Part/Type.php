<?php

/**
 * по типу
 * @author markov
 */
class Query_Part_Type extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.type',
			$this->params['value']);
	}
}