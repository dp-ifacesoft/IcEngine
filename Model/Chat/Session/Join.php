<?php

class Chat_Session_Join extends Model
{
	/**
	 * @desc Создает джоин для текущего пользователя
	 * @param Model $session
	 * @param string $name
	 * @param string $uri
	 * @return Model
	 */
	public static function forUser (Model $session, $name, $uri)
	{
		$join = new self (array (
			'Chat_Session__id'		=> $session->key (),
			'name'					=> $name,		
			'phpSessionId'			=> User_Session::getCurrent ()->phpSessionId,
			'uri'					=> $uri
		));
		return $join->save ();
	}
}

