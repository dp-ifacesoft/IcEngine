<?php

class Subscribe_Subscriber_Attribute extends Model
{
	/**
	 * @desc Создать атрибуты для подписчика
	 * @param Model $subscriber
	 * @param array $attributes
	 */
	public static function createFor (Model $subscriber, array $attributes)
	{
		foreach ($attributes as $key=>$value)
		{
			$attribute = new self (array (
				'Subscribe_Subscriber__id'	=> $subscriber->key (),
				'key'						=> $key,
				'value'						=> $value
			));
			$attribute->save ();
		}
	}
	/**
	 * @desc Удалить все атрибуты для подписчика
	 * @param Model $subscriber
	 * @param array $keys
	 */
	public static function deleteFor (Model $subscriber, array $keys = array ())
	{
		$query = Query::instance ()
			->delete ()
			->from ('Subscribe_Subscriber_Attribute')
			->where ('Subscribe_Subscriber__id', $subscriber->key ());
			
		if ($keys)
		{
			$query->where ('key', $keys);
		}
			
		DDS::execute ($query);
	}
}