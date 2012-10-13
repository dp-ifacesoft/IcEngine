<?php
/**
 * 
 * @desc Опция проверяет, может ли пользователь добавлять связь с этим API.
 * Т.е. может ли быть аккаунт пользователя на сайте быть связан с аккаунтом
 * сервиса, представляюего это API.
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Api_Option_User_Attach extends Model_Option
{
	
	public function before ()
	{
		$is = isset ($this->params ['is'])
			? $this->params ['is']
			: 1;
		$this->query->where ('userAttach', $is);
	}
	
}
