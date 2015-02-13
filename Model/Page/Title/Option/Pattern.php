<?php

/**
 * По паттерну урла
 *
 * @author morph
 */
class Page_Title_Option_Pattern extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where('? RLIKE pattern', $this->params['value']);
	}
}