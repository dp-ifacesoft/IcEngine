<?php

/**
 * 
 * @author markov
 */
class Query_Part_Sort extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		if (
			isset ($this->params['order']) &&
			strtoupper ($this->params['order']) == 'DESC'
		)
		{
			$this->query->order (
				'`' . $this->modelName . '`.`sort` DESC'
			);
		}
		else
		{
			$this->query->order (
				'`' . $this->modelName . '`.`sort`'
			);
		}
	}
}