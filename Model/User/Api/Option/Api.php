<?php
/**
 * 
 * @desc Опция для выбора данных по системе
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class User_Api_Option_Api extends Model_Option
{
	
	public function before ()
	{
		$this->query->where ('Api__id', $this->params ['id']);
	}
	
}
