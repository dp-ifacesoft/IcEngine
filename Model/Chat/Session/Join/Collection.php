<?php

class Chat_Session_Join_Collection extends Model_Collection
{
	public function other ()
	{
		$sessions = $this->column ('Chat_Session__id');

		$ids = $this->column ('id');

		$query = Query::instance ()
			->where ('Chat_Session__id', $sessions)
			->where ('id NOT IN (?)', $ids);

		$other_join_collection = Model_Collection_Manager::byQuery (
			'Chat_Session_Join',
			$query
		);
		
		return $other_join_collection;
	}
}