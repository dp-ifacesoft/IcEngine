<?php

/**
 * По хосту
 *
 * @author dp
 */
class Page_Title_Option_Host extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
        $this->query->where('(? RLIKE `host` OR `host`="")', $this->params['value']);
	}
}