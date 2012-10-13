<?php

class Chat_Message_Option_Session extends Model_Option
{
	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before ()
	{
		$this->query
			->innerJoin (
				'Chat_Session_Join',
				'Chat_Session_Join.id=Chat_Message.Chat_Session_Join__id'
			)
			->where (
				'Chat_Session_Join.Chat_Session__id', $this->params ['id']
			);
	}
}