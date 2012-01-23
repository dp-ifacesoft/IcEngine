<?php

namespace Ice;

/**
 *
 * @desc Для выбора сессии чата по адресу страницы
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Chat_Session_Join_Option_Uri extends Model_Option
{

	public function before ()
	{
		$this->query->where ('uri', $this->params ['uri']);
	}

}
