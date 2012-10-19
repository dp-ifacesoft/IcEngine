<?php
/**
 *
 * @desc Абстрактный класс какого-либо API
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Api_Abstract extends Model_Factory_Delegate
{

	public function log ($text)
	{
		$log = new Api_Log (array (

		));
		$log->save ();
	}

	/**
	 * @desc Возвращает данные о связи пользователя с API
	 * @param User $user Пользователь
	 * @return User_Open_Api_Abstract
	 */
	public function userAttachment (User $user)
	{
		return Model_Manager::byOptions (
			'User_Api',
			array (
				'name'	=> 'Api',
				'id'	=> $this->id
			)
		);
	}

}