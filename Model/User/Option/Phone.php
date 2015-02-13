<?php

/**
 * Опшен получения юзера по телефону
 *
 * @author neon
 */
class User_Option_Phone extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		if ($this->params['value']) {
			$this->query
				->where('phone', $this->params['value']);
		}
	}
}