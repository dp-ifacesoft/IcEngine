<?php

class Subscribe_Subscriber_Option_Join extends Model_Option
{

	public function before ()
	{
		$this->query
			->innerJoin (
				'Subscribe_Subscriber_Join',
				'Subscribe_Subscriber_Join.Subscribe_Subscriber__id=Subscribe_Subscriber.id'
			)
			->where (
				'Subscribe_Subscriber_Join.Subscribe__id',
				$this->params ['id']
			)
			->where ('Subscribe_Subscriber.active', 1);
			
		if (isset ($this->params ['active']))
		{
			$this->query
				->where (
					'Subscribe_Subscriber_Join.active', 
					(int) $this->params ['active']
			);
		}
	}
}