<?php

/**
 *
 * @author markov
 */
class Query_Part_Limit extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->limit (
			(int) $this->params ['count'],
			isset ($this->params ['offset']) ? $this->params ['offset'] : null
		);
	}
}