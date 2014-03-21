<?php

/**
 * Часть запроса для опшина Content_Category
 *
 * @author markov
 */
class Query_Part_Content_Category extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		if (isset ($this->params['id']))
		{
			$this->query
				->where ('Content_Category__id', $this->params['id']);
		}
	}
}