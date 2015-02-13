<?php

/**
 * Часть запроса для поля isMain = 1
 *
 * @author markov
 */
class Query_Part_Main extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.isMain', 1);
	}
}