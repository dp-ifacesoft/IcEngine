<?php

/**
 * Опшен для получения юзера по логину
 *
 * @author neon
 */
class User_Option_Login extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		if ($this->params['value']) {
			$this->query
				->where('login', $this->params['value']);
		}
	}
}