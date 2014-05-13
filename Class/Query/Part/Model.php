<?php

/**
 *
 * @author markov
 */
class Query_Part_Model extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where(
			$this->collection->modelName . '.model',
			$this->params['model']
		);
	}
}