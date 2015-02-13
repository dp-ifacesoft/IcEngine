<?php

/**
 * 
 * @author markov
 */
class Query_Part_Url extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.url', $this->params['url']);
	}
}