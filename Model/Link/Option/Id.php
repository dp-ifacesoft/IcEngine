<?php

/**
 * Для выбора Id линка
 * 
 * @author goorus, apostle
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
			->where('toRowId=?', $this->params['toTableId'])
			->where('fromTable=?', $this->params['fromTable'])
			->where('fromRowId=?', $this->params['fromTableId']);
	}
}