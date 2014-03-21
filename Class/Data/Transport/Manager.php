<?php
/**
 * 
 * @desc Менеджер транспортов
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Data_Transport_Manager extends Manager_Abstract
{
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Транспорты
		 * @var array
		 */
		'transports'	=> array (
			'cli_input'		=> array (
				'providers'	=> array (
					'Cli'
				)
			),
			/**
			 * @desc транспорт входных данные по умолчанию
			 * @var array
			 */
			'default_input'	=> array (
				/**
				 * @desc Провайдеры, входящие в транспорт
				 * @var array
				 */
				'providers'	=> array (
					'Router',
					'Request',
					'Session'
				)
			),
			/**
			 * @desc Транспорт исходящих данных
			 * @var array
			 */
			'default_output'	=> array ()
		)
	);
	
	/**
	 * @desc Инициализированные транспорты.
	 * @var array
	 */
	protected static $_transports = array ();
	
	/**
	 * @desc 
	 * @param string $name
	 * @return array
	 */
	public static function configFor ($name)
	{
		$config = self::config ();
		$config = $config ['transports'][$name];
		
		// Алиасы
		while (is_string ($config))
		{
			$config = $config ['transports'][$config];
		}
		
		return $config;
	}
	
	/**
	 * @desc 
	 * @param string $name
	 * @return Data_Transport
	 */
	public static function get ($name)
	{
		if (isset (self::$_transports [$name]))
		{
			return self::$_transports [$name];
		}
		
		$cfg = self::configFor ($name);
		
		$transport = new Data_Transport ();
		
		if (isset ($cfg ['providers']))
		{
			foreach ($cfg ['providers'] as $provider)
			{
				$transport->appendProvider (
					Data_Provider_Manager::get ($provider)
				);
			}
		}
		
		return self::$_transports [$name] = $transport;
	}
	
}