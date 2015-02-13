<?php

/**
 * Часть запроса для опшина Content
 *
 * @author markov
 */
class Query_Part_Content extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		if (isset ($this->params['key']))
		{
			$this->query
				->where ('Content__id', $this->params['key']);
		}
	}
}