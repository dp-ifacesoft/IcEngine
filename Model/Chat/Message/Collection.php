<?php

class Chat_Message_Collection extends Model_Collection
{
	/**
	 * @desc Вернуть данные сообщения
	 * @return Ambigous <multitype:, multitype:NULL mixed >
	 */
	public function serialize ()
	{
		$result = array ();
		
		foreach ($this as $item)
		{
			$result [] = array (
				'name'			=> $item->Chat_Session_Join->name,
				'join_id'		=> $item->Chat_Session_Join->key (),
				'session_id'	=> $item->Chat_Session_Join->Chat_Session__id,
				'date'			=> date ('H:i', Helper_Date::strToTimestamp (
					$item->createdAt
				)),
				'message'		=> $item->message,
				'id'			=> $item->key ()
			);
		}
		
		return $result; 
	}
}