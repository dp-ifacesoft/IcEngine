<?php
/**
 * Часть запроса для  Not_Id
 *
 * @author markov
 */
class Query_Part_Not_Id extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		if (isset($this->params['ids']) && $this->params['ids'])
		{
			$this->query->where(
				$this->modelName . '.id NOT IN (?)',
				array($this->params['ids'])
			);
		}
		if (isset($this->params['id']) && $this->params['id'])
		{
			$this->query->where(
				$this->modelName . '.id != ?',
				array ($this->params['id'])
			);
		}
	}
}