<?php
/**
 * 
 * @desc Менеджер для работы с сессиями.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Session_Manager
{
	
	/**
	 * @desc Провайдер данных.
	 * @var Data_Provider_Abstract
	 */
	public static $provider;
	
	/**
	 * @desc 
	 * @var string
	 */
	public static $sessSavePath;
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Время жизни сессии
		 * @var integer
		 */
		'TTL'			=> 3600,
		/**
		 * @desc Используемый провайдер
		 * @var string
		 */
		'provider'		=> null,
	);
 
	/**
	 * @desc Close function, this works like a destructor in classes and is executed when the session operation is done.
	 */
	public static function close ()
	{
		return true;
	}
	
	/**
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$_config))
		{
			self::$_config = Config_Manager::get (__CLASS__, self::$_config);
		}
		return self::$_config;
	}
 
	/**
	 * @desc The destroy handler, this is executed when a session is destroyed with session_destroy() and takes the session id as its only parameter.
	 */
 	public static function destroy ($id)
 	{
 		self::$provider->delete ($id);
 		return true;
 	}
 
	/**
	 * @desc The garbage collector, this is executed when the session garbage collector is executed and takes the max session lifetime as its only parameter.
	 */
	public static function gc ($maxlifetime)
	{
	}
	
	/**
	 * @desc Инициализация менеджера сессий
	 */
	public static function init ()
	{
		$config = self::config ();
		if ($config ['provider'])
		{
			self::initProvider (
				Data_Provider_Manager::get ($config ['provider'])
			);
		}
	}
	
	/**
	 * @desc 
	 * @param Data_Provider_Abstract $provider
	 */
	public static function initProvider (Data_Provider_Abstract $provider)
	{
		self::$provider = $provider;
		session_set_save_handler (
			__CLASS__ . '::open',
			__CLASS__ . '::close',
			__CLASS__ . '::read',
			__CLASS__ . '::write',
			__CLASS__ . '::destroy',
			__CLASS__ . '::gc'
		);
	}
	
	/**
	 * @desc Open function, this works like a constructor in classes and
	 * is executed when the session is being opened. The open function expects 
	 * two parameters, where the first is the save path and the second is 
	 * the session name.
	 * @param string $save_path
	 * @param string $session_name
	 */
	public static function open ($save_path, $session_name)
	{
		self::$sessSavePath = $save_path;
		return true;
	}
	
	/**
	 * @desc Read function must return string value always to make save handler 
	 * work as expected. Return empty string if there is no data to read. 
	 * Return values from other handlers are converted to boolean expression. 
	 * TRUE for success, FALSE for failure.
	 */
	public static function read ($id)
	{
		return (string) self::$provider->get ($id);
	}
	
	/**
	 * @desc Write function that is called when session data is to be saved.
	 * This function expects two parameters: an identifier and the data 
	 * associated with it.
	 * Note: 
	 * The "write" handler is not executed until after the output stream 
	 * is closed. Thus, output from debugging statements in the "write" handler 
	 * will never be seen in the browser. If debugging output is necessary,
	 * it is suggested that the debug output be written to a file instead.
	 */
	public static function write ($id, $data)
	{
		self::$provider->set ($id, $data, (int) self::config ()->TTL);
		return true;
	}
	
}
