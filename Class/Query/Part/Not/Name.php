<?php

/**
 * по имени
 * @author markov
 */
class Query_Part_Not_Name extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		if (!is_array($this->params['value'])) {
			$this->query->where('name != ?', $this->params['value']);
		} else {
			$this->query->where('name NOT IN (?)', $this->params['value']);
		}
	}
}