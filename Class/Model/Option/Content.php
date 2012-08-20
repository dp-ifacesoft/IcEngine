<?php

/**
 * Опшен для получения чего-либо по контенту
 *
 * @author neon
 */

class Model_Option_Content extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		if (isset ($this->params['key']))
		{
			$this->query
				->where ('Content__id', $this->params['key']);
		}
	}
}