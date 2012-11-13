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
		$keyField = $this->modelName 
			? Model_Scheme::keyField($this->modelName) : 'id';
		$field = $this->modelName . '.' . $keyField;
		$this->query->where($field, $this->params['key']);
	}
}

