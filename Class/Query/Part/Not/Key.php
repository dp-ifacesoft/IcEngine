<?php

/**
 *
 * @author markov
 */
class Query_Part_Not_Key extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query() 
	{
		$key = $this->params['key'];
		$modelName = $this->modelName;
		$keyField = Model_Scheme::keyField($modelName);
		if (!is_array($key)) {
			$this->query->where($modelName . '.' . $keyField . ' != ?',
				array($key));
		} else {
			$this->query->where($modelName . '.' . $keyField . ' NOT IN (?)',
				array($key));
		}
	}
}

