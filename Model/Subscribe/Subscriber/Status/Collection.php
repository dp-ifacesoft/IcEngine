<?php

class Subscribe_Subscriber_Status_Collection extends Model_Collection
{
	public function forSession ($session_id, $status)
	{
		$this
			->query ()
			->where ('Subscribe_Subscriber_Status.Subscribe_Session__id', $session)
			->where ('Subscribe_Subscriber_Status.status', $status);
	}
}