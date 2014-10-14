<?php

/**
 *
 * @author markov
 */
class Query_Part_Parent extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.parentId',
			$this->params['id']);
	}
}