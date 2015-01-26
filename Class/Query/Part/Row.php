<?php

/**
 * 
 * @author markov
 */
class Query_Part_Row extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName. '.rowId',
			$this->params['id']);
	}
}