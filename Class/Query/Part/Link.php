<?php

/**
 *
 * @author markov
 */
class Query_Part_Link extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.link',
			$this->params['link']);
	}
}