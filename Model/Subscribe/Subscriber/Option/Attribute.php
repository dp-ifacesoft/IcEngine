<?php

class Subscribe_Subscriber_Option_Attribute extends Model_Option
{

	public function before ()
	{
		$this->query
			->innerJoin (
				'Subscribe_Subscriber_Attribute',
				'Subscribe_Subscriber_Attribute.Subscribe_Subscriber__id=
					Subscribe_Subscriber.id'
			)
			->where (
				'Subscribe_Subscriber_Attribute.key', 
				$this->params ['key']
			)
			->where (
				'Subscribe_Subscriber_Attribute.value', 
				$this->params ['value']
			);
	}
}