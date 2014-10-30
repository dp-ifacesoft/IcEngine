<?php

/**
 * query part для Random
 *
 * @author Apostle
 */
class Query_Part_Random extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->order('rand()'. '+' . rand(1, 1000));
        if (isset($this->params['count'])) {
            $this->query->limit(
                $this->params['count']);
        }
	}
}

