<?php

namespace Ice;

/**
 *
 * @desc Опция для выбора данных по пользователю
 * @author Юрий Шведов
 * @package Ice
 *
 */
class User_Api_Option_User extends Model_Option
{

	public function before ()
	{
		$this->query->where ('User__id', $this->params ['id']);
	}

}
