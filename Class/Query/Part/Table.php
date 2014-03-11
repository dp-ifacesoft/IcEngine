<?php

/**
 * 
 * @author markov
 */
class Query_Part_Table extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.table', $this->params['table']);
	}
}