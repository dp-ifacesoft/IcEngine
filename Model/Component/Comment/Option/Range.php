<?php

class Component_Comment_Option_Range extends Model_Option
{
	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before ()
	{
		$st = !empty ($this->params ['st']) ? $this->params ['st'] : 0;
		$count = !empty ($this->params ['count']) ? $this->params ['count'] : 0;

		$this->query
			->limit ($count, $st);
	}
}