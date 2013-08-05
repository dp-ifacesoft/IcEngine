<?php

/**
 * Для выбора Id линка
 * 
 * @author goorus, morph
 */
class Link_Option_Id extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query
			->where('toTable=?', $this->params['toTable'])
			->where('toTableId=?', $this->params['toTableId'])
			->where('fromTable=?', $this->params['fromTable'])
			->where('fromTableId=?', $this->params['fromTableId']);
	}
}