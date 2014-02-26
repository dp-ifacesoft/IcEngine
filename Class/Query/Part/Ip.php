<?php

/**
 * Description of Ip
 *
 * @author markov
 */
class Query_Part_Ip extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.ip', $this->params['ip']);
	}
}
