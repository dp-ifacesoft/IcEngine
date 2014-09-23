<?php

/**
 * 
 * @author markov
 */
class Query_Part_Show extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.show', 1);
	}
}