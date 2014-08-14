<?php

class Controller_Chat_Session extends Controller_Abstract
{
	public function create ()
	{
		$code = $this->_input->receive ('code');

		$session = new Chat_Session (array (
			'code'		=> $code,
			'createdAt'	=> Helper_Date::toUnix ()
		));

		$session->save ();

		$this->_task->setTemplate (null);
	}

	public function join ()
	{
		list (
			$code,
			$name,
			$uri
		) = $this->_input->receive (
			'code',
			'name',
			'uri'
		);

		$session_join = Registry::sget ('session_join');

		$data = array ();

		if ($session_join)
		{
			$name = $session_join->name;
			$code = $session_join->Chat_Session->code;
		}

		$session = Chat_Session::byCode ($code);

		if (!$session)
		{
			$session = new Chat_Session (array (
				'code'		=> $code,
				'createdAt'	=> Helper_Date::toUnix ()
			));

			$session->save ();
		}

		$query = Query::instance ()
			->where (
				'phpSessionId',
				User_Session::getCurrent ()->phpSessionId
			)
			->where (
				'Chat_Session__id',
				$session->key ()
			);

		if ($uri)
		{
			$query
				->where ('uri', $uri);
		}

		$join = Model_Manager::byQuery (
			'Chat_Session_Join',
			$query
		);

		$name = Helper_String::charset_x_utf8 ($name);

		if (!$join)
		{
			$join = Chat_Session_Join::forUser (
				$session,
				$name,
				$uri
			);
		}

		$companion = Model_Manager::byQuery (
			'Chat_Companion',
			Query::instance ()
				->where ('phpSessionId', User_Session::getCurrent ()->phpSessionId)
				->where ('Chat_Session_Join__id', 0)
		);

		if ($companion)
		{
			$companion->update (array (
				'Chat_Session_Join__id'	=> $join->key ()
			));
		}

		$this->_output->send (array (
			'data'	=> array (
				'join_id'		=> $join->key (),
				'session_id'	=> $session->key (),
				'name'			=> $name,
				'code'			=> $code,
				'uri'			=> $uri
			)
		));
	}

	/**
	 * @desc Получаем открытые чаты
	 * @return void
	 */
	public function sessionsList ()
	{
		// Все джоины пользователя
		$join_collection = Model_Collection_Manager::byQuery (
			'Chat_Session_Join',
			Query::instance ()
				->where ('phpSessionId', User_Session::getCurrent ()->phpSessionId)
		);

		if (!$join_collection->count ())
		{
			return;
		}

		$my_ids = $join_collection->column ('id');

		$other_joins = array ();

		// Джоины собеседников пользователя
		$other_join_collection = $join_collection->other ();

		// Определяем к каким сессиям уже подключился собеседник
		foreach ($other_join_collection as $join)
		{
			$other_joins [$join->Chat_Session__id] = $join;
		}

		$data = array ();

		// Получаем коллекцию имен собеседников - на тот случай,
		// когда джоин собеседника еще не создан
		$companion_collection = Model_Collection_Manager::byQuery (
			'Chat_Companion',
			Query::instance ()
				->where ('Chat_Session_Join__id', $my_ids)
		);

		foreach ($join_collection as $i => $join)
		{
			$session_id = $join->Chat_Session__id;

			$companion = $companion_collection->filter (array (
				'Chat_Session_Join__id' => $join->key ()
			))
				->first ();

			if (!$companion)
			{
				continue;
			}

			$data [$join->Chat_Session__id] = array (
				'session_id'	=> $session_id,
				'name'			=> $companion->name,
				'companion_id'	=> $companion->rowId,
				'my_join_id'	=> $join->key (),
				'has_message'	=> 0,
				'join_id'		=> isset ($other_joins [$session_id])
					? $other_joins [$session_id] : null
			);
		}

		$other_ids = $other_join_collection->column ('id');

		// Получаем все непрочитанные сообщения собеседников
		$message_collection = Model_Collection_Manager::byQuery (
				'Chat_Message',
				Query::instance ()
					->where ('Chat_Session_Join__id', $other_ids)
					->where ('readed', 0)
					->group ('Chat_Session_Join__id')
			);

		// Узнаем были ли новые сообщения от конкретного собеседника
		foreach ($message_collection as $message)
		{
			$join = $message->Chat_Session_Join;

			$data [$join->Chat_Session__id]['has_message'] = 1;
		}

		// Прочитываем все сообщения
		$message_collection->update (array (
			'readed'	=> 1
		));

		$this->_task->setTemplate (null);

		return $this->_output->send (array (
			'data'	=> array (
				'sessions'	=> array_values ($data)
			)
		));
	}
}