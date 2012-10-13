<?php

class Subscribe_Subscriber_Collection extends Model_Collection
{
	/**
	 * 
	 * @desc Получить атрибуты подписчика
	 * @param Model $subscriber
	 * @param string $key
	 * @return Model
	 */
	public static function getFor (Model $subscriber, $key = null)
	{
		$this
			->query ()
			->where ('Subscribe_Subscriber__id', $subscriber);
		
		if (!is_null ($key))
		{
			$this
				->query ()
				->where ('key', $key);
		}
		
		return $this;
	}
}