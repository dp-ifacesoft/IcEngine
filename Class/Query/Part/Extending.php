<?php

/**
 * Часть запроса для опшина Extending
 *
 * @author markov
 */
class Query_Part_Extending extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		if (isset ($this->params['value']))
		{
			$this->query
				->where ('extending', $this->params['value']);
		}
	}
}