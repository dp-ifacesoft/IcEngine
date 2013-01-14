<?php
/**
 * 
 * @desc Для выбора сессии чата по идентификатору сессии пользователя
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Chat_Session_Join_Option_Session_Id extends Model_Option
{
	
	public function before ()
	{
		$this->query->where ('phpSessionId', $this->params ['id']);
	}
	
}

