<?php

/**
 * Description of Key
 *
 * @author markov
 */
class Query_Part_Key extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$field = $this->modelName . '.id';
		$this->query->where($field, $this->params['key']);
	}
}

