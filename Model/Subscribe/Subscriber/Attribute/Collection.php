<?php

class Subscribe_Subscriber_Attribute_Collection extends Model_Collection
{
	/**
	 * 
	 * @param Model $Subscriber
	 * @param string $key
	 * @return Model_Collection
	 */
	public function forSubscriber (Model $subscriber, $key)
	{
		return new Model_Collection (DDS::execute (
			Query::instance ()
				->from ('Subscribe_Subscriber_Attribute')
				->where ('Subscribe_Subscriber__id', $subscriber->key ())
				->where ('key', $key)
			)
				->asColumn ($key)
		);
	}
}