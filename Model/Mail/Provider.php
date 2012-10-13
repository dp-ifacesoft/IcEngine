<?php
/**
 * 
 * @desc Класс-фабрика провайдера сообщений
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Provider extends Model_Factory
{
	public static function byName ($name)
	{
		return Model_Manager::byQuery (
			'Mail_Provider',
			Query::instance ()
				->where ('name', $name)
		);
	}
}