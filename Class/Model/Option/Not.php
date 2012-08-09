<?php

/**
 * Опшен фильтрации
 * поле != значению
 *
 * @author neon
 */
class Model_Option_Not extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		if ($this->params['field'] && $this->params['value'])
		{
			$this->query
				->where ($this->params['field'] . ' != ?', $this->params['value']);
		}
	}
}