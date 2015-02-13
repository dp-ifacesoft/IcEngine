<?php

/**
 * 
 * @author markov
 */
class Query_Part_Value extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.value', $this->params['value']);
	}
}