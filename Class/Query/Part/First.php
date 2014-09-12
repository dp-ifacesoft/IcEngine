<?php

/**
 * Часть запроса для  первого поля
 *
 * @author markov
 */
class Query_Part_First extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->limit (1, 0);
	}
}