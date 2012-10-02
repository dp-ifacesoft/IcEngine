<?php

/**
 * Опшен выбора чего-то по текущему юзеру
 *
 * @author neon
 */
class Model_Option_User extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query
			->where('User__id', User::getCurrent()->key());
	}
}