<?php

/**
 * Опшен для получения коллекции компонентов
 *
 * @author neon
 */
class Component_Comment_Option_Link extends Model_Option
{

	/**
	 * @inheritdoc
	 */
	public function before ()
	{
		if (isset ($this->params['table']))
		{
			$this->query
				->where ('table', $this->params['table']);
		}
		if (isset ($this->params['rowId']))
		{
			$this->query
				->where ('rowId', $this->params['rowId']);
		}
	}
}