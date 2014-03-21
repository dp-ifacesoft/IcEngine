<?php

/**
 * 
 * @author viktor
 */
class Query_Part_City extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        $cityId = $this->params['id'];
		$this->query->where($this->modelName . '.City__id', $cityId);
	}
}